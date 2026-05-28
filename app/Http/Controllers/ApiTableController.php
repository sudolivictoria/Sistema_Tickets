<?php

namespace App\Http\Controllers;

use App\Models\Manual;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ApiTableController extends Controller
{
    private const TIPOS_VALIDOS = ['dashboard', 'usuario', 'asignar', 'mis_tickets', 'mis_asignados', 'historial'];
    private const TIPOS_SOLO_CONTENIDO = ['recursos'];
    private const TIPOS_SOLO_STAFF = ['asignar', 'historial', 'mis_asignados'];
    private const ESTADOS_CERRADOS = [3, 4, 5];
    /**
     * Endpoint de Flujo de Eventos del Servidor (SSE) en Tiempo Real
     */
    public function sseStream(Request $request): StreamedResponse
    {
        $user = Auth::user();
        $miUnidadId = $user ? (int) $user->unidad_id : 0;
        $userId = $user ? $user->id : 0;
        $userRolId = $user ? $user->rol_id : 2;

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        return response()->stream(function () use ($request, $user, $miUnidadId, $userId, $userRolId) {
            if (function_exists('ob_end_clean')) {
                @ob_end_clean();
            }
            if (!$user) {
                echo "data: " . json_encode(['error' => 'No autenticado']) . "\n\n";
                if (ob_get_level() > 0) ob_flush();
                flush();
                return;
            }
            $tipo = (string) $request->query('tipo', 'dashboard');
            $estadoFiltro = strtolower(trim((string) $request->query('estado', 'todos')));
            $añoActual = (int) date('Y');
            try {
                $currentUser = User::find($userId);
                if ($currentUser) {
                    $queryTickets = Ticket::with(['user.unidad', 'estado', 'prioridad', 'tecnico', 'tipo_solicitud', 'categoria']);

                    //--Filtros según contexto de rol y tipo de vista
                    $this->aplicarFiltrosBase($queryTickets, $currentUser, $tipo, $miUnidadId);

                    //--Filtro estricto por el estado solicitado
                    $this->inyectarFiltroEstado($queryTickets, $estadoFiltro, $tipo);

                    $ticketsResult = $queryTickets->latest()->get();
                    if ($tipo === 'usuario') {
                        $ticketsResult = $ticketsResult->take(5);
                    }

                    //--Contadores de estadísticas superiores
                    $statsBase = function () use ($añoActual, $currentUser, $tipo, $miUnidadId) {
                        $q = Ticket::whereYear('created_at', $añoActual);
                        $this->aplicarFiltrosStats($q, $currentUser, $tipo, $miUnidadId);
                        return $q;
                    };

                    $contadores = [
                        'abiertos'  => $statsBase()->whereNull('tecnico_id')->whereNotIn('estado_id', self::ESTADOS_CERRADOS)->count(),
                        'proceso'   => $statsBase()->whereNotNull('tecnico_id')->where('estado_id', 2)->count(),
                        'resueltos' => $statsBase()->whereIn('estado_id', self::ESTADOS_CERRADOS)->count(),
                    ];

                    $graficoHtml = ($userRolId != 2) ? $this->generarGrafico($miUnidadId, $añoActual) : null;
                    $contadorMisAsignados = Ticket::where('tecnico_id', $userId)->where('estado_id', 2)->count();

                    [$cargaTrabajo, $resueltos24h, $tasaCierre] = ($tipo === 'historial')
                        ? $this->calcularMetricas($currentUser, $miUnidadId, $añoActual)
                        : [0, 0, 0];

                    $htmlTabla = $this->renderizarVista($tipo, $ticketsResult, $miUnidadId);

                    echo "data: " . json_encode([
                        'html'              => $htmlTabla,
                        'contadores'        => $contadores,
                        'grafico'           => $graficoHtml,
                        'contadorAsignados' => (int) $contadorMisAsignados,
                        'cargaTrabajo'      => (int) $cargaTrabajo,
                        'resueltos24h'      => (int) $resueltos24h,
                        'tasaCierre'        => (int) $tasaCierre,
                    ]) . "\n\n";
                }
            } catch (Throwable $e) {
                Log::error('ApiTableController SSE Error: ' . $e->getMessage());
                echo "data: " . json_encode(['error' => 'Error interno de procesamiento']) . "\n\n";
            }

            if (ob_get_level() > 0) ob_flush();
            flush();
        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache',
            'Connection'        => 'keep-alive',
            'X-Accel-Buffering' => 'no'
        ]);
    }

    /**
     * Endpoint HTTP Clásico de consulta / fallback manual
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'No autenticado.'], 401);
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        $tipo = (string) $request->query('tipo', 'dashboard');
        if (!in_array($tipo, self::TIPOS_VALIDOS, true) && !in_array($tipo, self::TIPOS_SOLO_CONTENIDO, true)) {
            return response()->json(['error' => 'Tipo no válido.'], 422);
        }
        if (in_array($tipo, self::TIPOS_SOLO_CONTENIDO, true)) {
            return response()->json(['html' => $this->renderizarVista($tipo, collect(), 0)]);
        }
        if ($user->rol_id == 2 && in_array($tipo, self::TIPOS_SOLO_STAFF, true)) {
            return response()->json(['error' => 'Acceso denegado.'], 403);
        }
        try {
            $miUnidadId = (int) $user->unidad_id;
            $añoActual  = (int) date('Y');
            $estadoFiltro = strtolower(trim((string) $request->query('estado', 'todos')));

            $queryTickets = Ticket::with(['user.unidad', 'estado', 'prioridad', 'tecnico', 'tipo_solicitud', 'categoria']);

            $this->aplicarFiltrosBase($queryTickets, $user, $tipo, $miUnidadId);
            $this->inyectarFiltroEstado($queryTickets, $estadoFiltro, $tipo);

            $ticketsResult = $queryTickets->latest()->get();
            if ($tipo === 'usuario') {
                $ticketsResult = $ticketsResult->take(5);
            }

            $statsBase = function () use ($añoActual, $user, $tipo, $miUnidadId) {
                $q = Ticket::whereYear('created_at', $añoActual);
                $this->aplicarFiltrosStats($q, $user, $tipo, $miUnidadId);
                return $q;
            };

            $contadores = [
                'abiertos'  => $statsBase()->whereNull('tecnico_id')->whereNotIn('estado_id', self::ESTADOS_CERRADOS)->count(),
                'proceso'   => $statsBase()->whereNotNull('tecnico_id')->where('estado_id', 2)->count(),
                'resueltos' => $statsBase()->whereIn('estado_id', self::ESTADOS_CERRADOS)->count(),
            ];

            $graficoHtml = ($user->rol_id != 2) ? $this->generarGrafico($miUnidadId, $añoActual) : null;
            $contadorMisAsignados = Ticket::where('tecnico_id', $user->id)->where('estado_id', 2)->count();

            [$cargaTrabajo, $resueltos24h, $tasaCierre] = ($tipo === 'historial')
                ? $this->calcularMetricas($user, $miUnidadId, $añoActual)
                : [0, 0, 0];

            return response()->json([
                'html'              => $this->renderizarVista($tipo, $ticketsResult, $miUnidadId),
                'contadores'        => $contadores,
                'grafico'           => $graficoHtml,
                'contadorAsignados' => (int) $contadorMisAsignados,
                'cargaTrabajo'      => (int) $cargaTrabajo,
                'resueltos24h'      => (int) $resueltos24h,
                'tasaCierre'        => (int) $tasaCierre,
            ]);
        } catch (Throwable $e) {
            Log::error('ApiTableController Refresh Error: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno.'], 500);
        }
    }
    /**
     * Mapeo estricto del estado solicitado para inyección SQL limpia
     */
    private function inyectarFiltroEstado($queryTickets, string $estadoFiltro, string $tipo): void
    {
        if ($estadoFiltro === 'todos' || $estadoFiltro === '') {
            return;
        }

        switch ($estadoFiltro) {
            case '1':
            case 'abierto':
                // 🎯 FIX: Si es dashboard admin aplica lógica de negocio, 
                // pero si es cliente o cualquier otra vista, busca por ID de estado o por ausencia de cierre
                if ($tipo === 'dashboard') {
                    $queryTickets->whereNull('tecnico_id')->whereNotIn('estado_id', self::ESTADOS_CERRADOS);
                } else {
                    $queryTickets->where(function ($q) {
                        $q->where('estado_id', 1)->orWhereNotIn('estado_id', self::ESTADOS_CERRADOS);
                    });
                }
                break;

            case '2':
            case 'procesando':
            case 'proceso':
                if ($tipo === 'dashboard') {
                    $queryTickets->whereNotNull('tecnico_id')->where('estado_id', 2);
                } else {
                    $queryTickets->where('estado_id', 2);
                }
                break;

            case '3':
            case '4':
            case '5':
            case '3,4,5':
            case 'resuelto':
            case 'equivocado':
            case 'no corresponde':
                $queryTickets->whereIn('estado_id', self::ESTADOS_CERRADOS);
                break;

            default:
                if (is_numeric($estadoFiltro)) {
                    $queryTickets->where('estado_id', (int) $estadoFiltro);
                }
                break;
        }
    }
    private function aplicarFiltrosBase($query, $user, string $tipo, int $miUnidadId): void
    {
        if ($tipo === 'mis_tickets' || $user->rol_id == 2) {
            $query->where('user_id', $user->id);
            return;
        }
        if ($tipo === 'mis_asignados') {
            $query->where('tecnico_id', $user->id);
            return;
        }
        if ($tipo === 'historial') {
            if ($user->rol_id != 1) {
                $query->whereHas('categoria', fn($q) => $q->where('unidad_id', $miUnidadId));
            }
        } else {
            $query->whereHas('categoria', fn($q) => $q->where('unidad_id', $miUnidadId));
        }
        if ($tipo === 'asignar') {
            $query->where('estado_id', 1);
        }
        if ($tipo === 'dashboard') {
            $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
        }
    }

    private function aplicarFiltrosStats($query, $user, string $tipo, int $miUnidadId): void
    {
        if ($tipo === 'mis_tickets' || $user->rol_id == 2) {
            $query->where('user_id', $user->id);
            return;
        }
        if ($tipo === 'mis_asignados') {
            $query->where('tecnico_id', $user->id);
            return;
        }
        if ($tipo === 'historial' && $user->rol_id == 1) {
            return;
        }
        $query->whereHas('categoria', fn($q) => $q->where('unidad_id', $miUnidadId));
    }

    private function generarGrafico(int $miUnidadId, int $año): ?string
    {
        $nombresMeses = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];
        $statsMensuales = Ticket::selectRaw('MONTH(created_at) as mes, estado_id, COUNT(*) as total')
            ->whereYear('created_at', $año)
            ->whereHas('categoria', fn($q) => $q->where('unidad_id', $miUnidadId))
            ->groupBy('mes', 'estado_id')
            ->get();

        $mesesGrafico = [];
        for ($i = 1; $i <= 12; $i++) {
            $res   = $statsMensuales->where('mes', $i)->whereIn('estado_id', self::ESTADOS_CERRADOS)->sum('total');
            $pen   = $statsMensuales->where('mes', $i)->whereNotIn('estado_id', self::ESTADOS_CERRADOS)->sum('total');
            $total = $res + $pen;

            $mesesGrafico[] = [
                'nombre'         => $nombresMeses[$i - 1],
                'resueltos_pct'  => $total > 0 ? (int) round(($res / $total) * 100) : 0,
                'pendientes_pct' => $total > 0 ? (int) round(($pen / $total) * 100) : 0,
                'total'          => $total,
            ];
        }
        return view('partials.grafico_rendimiento', compact('mesesGrafico'))->render();
    }

    private function calcularMetricas($user, int $miUnidadId, int $año): array
    {
        $query = Ticket::whereYear('created_at', $año);
        if ($user->rol_id != 1) {
            $query->whereHas('categoria', fn($q) => $q->where('unidad_id', $miUnidadId));
        }
        $tickets = $query->get(['id', 'estado_id', 'created_at', 'fecha_cierre']);

        $cargaTrabajo = $tickets->filter(fn($t) => Carbon::parse($t->created_at)->isToday())->count();
        $resueltos24h = $tickets->whereIn('estado_id', self::ESTADOS_CERRADOS)
            ->filter(fn($t) => $t->fecha_cierre && Carbon::parse($t->fecha_cierre)->gte(now()->subDay()))
            ->count();
        $delMes     = $tickets->filter(fn($t) => Carbon::parse($t->created_at)->isCurrentMonth());
        $total      = $delMes->count();
        $cerrados   = $delMes->whereIn('estado_id', self::ESTADOS_CERRADOS)->count();
        $tasaCierre = $total > 0 ? (int) round(($cerrados / $total) * 100) : 0;
        return [$cargaTrabajo, $resueltos24h, $tasaCierre];
    }

    private function renderizarVista(string $tipo, $ticketsResult, int $miUnidadId): string
    {
        return match ($tipo) {
            'dashboard'   => view('partials.filas_dashboard', ['todosLosTickets' => $ticketsResult])->render(),
            'usuario'     => view('partials.filas_usuario', ['todosLosTickets' => $ticketsResult])->render(),
            'mis_tickets' => view('partials.filas_mis_tickets', ['misTickets' => $ticketsResult])->render(),
            'historial'   => view('partials.filas_historial', ['tickets' => $ticketsResult])->render(),
            'recursos'    => view('partials.filas_recursos', ['manuales' => Manual::with('categoria')->latest()->get()])->render(),
            'asignar', 'mis_asignados' => view("partials.filas_{$tipo}", [
                'tickets'  => $ticketsResult,
                'tecnicos' => User::where('unidad_id', $miUnidadId)->where('activo', true)->get(),
            ])->render(),
            default => '',
        };
    }
}
