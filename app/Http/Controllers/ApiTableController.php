<?php

namespace App\Http\Controllers;

use App\Models\Manual;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ApiTableController extends Controller
{
    private const TIPOS_VALIDOS = ['dashboard', 'usuario', 'asignar', 'mis_tickets', 'mis_asignados', 'historial'];
    private const TIPOS_SOLO_CONTENIDO = ['recursos'];
    private const TIPOS_SOLO_STAFF = ['dashboard', 'asignar', 'historial', 'mis_asignados'];
    private const ESTADOS_CERRADOS = [3, 4, 5];

    public function refresh(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'No autenticado.'], 401);
        }

        // Cierre temprano de sesión para evitar bloqueos por peticiones concurrentes AJAX
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $tipo = (string) $request->query('tipo', 'dashboard');
        if (!in_array($tipo, self::TIPOS_VALIDOS, true) && !in_array($tipo, self::TIPOS_SOLO_CONTENIDO, true)) {
            return response()->json(['error' => 'Tipo no válido.'], 422);
        }

        $miUnidadId = $user->unidad_id;

        if (in_array($tipo, self::TIPOS_SOLO_CONTENIDO, true)) {
            return response()->json(['html' => $this->renderizarVista($tipo, collect(), $miUnidadId)]);
        }

        if ($user->rol_id == 2 && in_array($tipo, self::TIPOS_SOLO_STAFF, true)) {
            return response()->json(['error' => 'Acceso denegado.'], 403);
        }

        try {
            $estadoFiltro = strtolower(trim((string) $request->query('estado', 'todos')));

            // =========================================================
            //    QUERY OPTIMIZADA CON SELECCIÓN DE COLUMNAS PUNTUALES
            // =========================================================
            $queryTickets = Ticket::with([
                'user:id,name,email,unidad_id', 
                'user.unidad:id,nombre_unidad',
                'estado:id,nombre_estado', 
                'prioridad:id,nombre_prioridad', 
                'tecnico:id,name', 
                'tipo_solicitud:id,nombre_tipo_solicitud', 
                'categoria:id,nombre_categoria,unidad_id'
            ]);

            $this->aplicarFiltrosTabla($queryTickets, $user, $tipo, $miUnidadId, $estadoFiltro);

            // Paginación segura para proteger memoria
            $limit = ($tipo === 'usuario') ? 5 : 150;
            $ticketsResult = $queryTickets->latest()->take($limit)->get();

            // =========================================================
            //     CONTADORES Y MÉTRICAS BÁSICAS
            // =========================================================
            $contadores = $this->calcularContadores($user, $tipo, $miUnidadId);

            // =========================================================
            //    GRÁFICOS, MÉTRICAS EXTRA Y PRIORIDADES EN 1 SOLO QUERY
            // =========================================================
            $añoActual = (int) date('Y');
            $graficoHtml = ($user->rol_id != 2 && $tipo === 'dashboard') 
                ? $this->generarGrafico($miUnidadId, $añoActual) 
                : null;

            $contadorMisAsignados = Ticket::where('tecnico_id', $user->id)
                ->where('estado_id', 2)
                ->count();

            [$cargaTrabajo, $resueltos24h, $tasaCierre] = ($tipo === 'historial')
                ? $this->calcularMetricasHistorial($user, $miUnidadId, $añoActual)
                : [0, 0, 0];

            // Optimización: Agrupación en una sola consulta SQL para prioridades
            $prioridades = $this->obtenerConteoPrioridades($miUnidadId);

            return response()->json([
                'html'              => $this->renderizarVista($tipo, $ticketsResult, $miUnidadId),
                'contadores'        => $contadores,
                'prioridades'       => $prioridades,
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

    private function aplicarFiltrosTabla($query, $user, string $tipo, $miUnidadId, string $estadoFiltro): void
    {
        if ($tipo === 'usuario' || $tipo === 'mis_tickets') {
            $query->where('user_id', $user->id);

            if ($estadoFiltro === 'todos' || $estadoFiltro === '') {
                return;
            }

            if ($estadoFiltro === 'resuelto,equivocado,no corresponde' || $estadoFiltro === 'cerrado') {
                $query->whereIn('estado_id', self::ESTADOS_CERRADOS);
            } else {
                if ($estadoFiltro === 'abierto' || $estadoFiltro === '1') {
                    $query->whereNull('tecnico_id')->whereNotIn('estado_id', self::ESTADOS_CERRADOS);
                } elseif ($estadoFiltro === 'procesando' || $estadoFiltro === '2') {
                    $query->whereNotNull('tecnico_id')->where('estado_id', 2);
                } else {
                    $query->whereNotIn('estado_id', self::ESTADOS_CERRADOS);
                }
            }
            return;
        }

        switch ($tipo) {
            case 'dashboard':
                if ($estadoFiltro === 'resuelto,equivocado,no corresponde' || $estadoFiltro === 'cerrado') {
                    $query->whereIn('estado_id', self::ESTADOS_CERRADOS)
                        ->whereMonth('created_at', date('m'))
                        ->whereYear('created_at', date('Y'));
                } else {
                    if ($estadoFiltro === 'abierto' || $estadoFiltro === '1') {
                        $query->whereNull('tecnico_id')->whereNotIn('estado_id', self::ESTADOS_CERRADOS);
                    } elseif ($estadoFiltro === 'procesando' || $estadoFiltro === '2') {
                        $query->whereNotNull('tecnico_id')->where('estado_id', 2);
                    } else {
                        $query->whereNotIn('estado_id', self::ESTADOS_CERRADOS);
                    }
                }

                if ($miUnidadId) {
                    $query->whereHas('categoria', fn($q) => $q->where('unidad_id', $miUnidadId));
                }
                break;

            case 'asignar':
                $query->where('estado_id', 1);
                if ($miUnidadId) {
                    $query->whereHas('categoria', fn($q) => $q->where('unidad_id', $miUnidadId));
                }
                break;

            case 'mis_asignados':
                $query->where('tecnico_id', $user->id)->where('estado_id', 2);
                break;

            case 'historial':
                $query->whereYear('created_at', date('Y'));
                if ($user->rol_id == 3 && $miUnidadId) {
                    $query->whereHas('categoria', fn($q) => $q->where('unidad_id', $miUnidadId));
                }
                break;
        }
    }

    private function calcularContadores($user, string $tipo, $miUnidadId): array
    {
        if ($user->rol_id == 2 || $tipo === 'usuario' || $tipo === 'mis_tickets') {
            $baseUserQuery = Ticket::where('user_id', $user->id);

            return [
                'abiertos'  => (clone $baseUserQuery)->whereNull('tecnico_id')->whereNotIn('estado_id', self::ESTADOS_CERRADOS)->count(),
                'proceso'   => (clone $baseUserQuery)->whereNotNull('tecnico_id')->where('estado_id', 2)->count(),
                'resueltos' => (clone $baseUserQuery)->whereIn('estado_id', self::ESTADOS_CERRADOS)
                    ->whereMonth('created_at', date('m'))
                    ->whereYear('created_at', date('Y'))->count(),
            ];
        }

        $baseStaffQuery = Ticket::query();
        if ($miUnidadId) {
            $baseStaffQuery->whereHas('categoria', fn($q) => $q->where('unidad_id', $miUnidadId));
        }

        return [
            'abiertos'  => (clone $baseStaffQuery)->whereNull('tecnico_id')->whereNotIn('estado_id', self::ESTADOS_CERRADOS)->count(),
            'proceso'   => (clone $baseStaffQuery)->whereNotNull('tecnico_id')->where('estado_id', 2)->count(),
            'resueltos' => (clone $baseStaffQuery)->whereIn('estado_id', self::ESTADOS_CERRADOS)
                ->whereMonth('created_at', date('m'))
                ->whereYear('created_at', date('Y'))->count(),
        ];
    }

    private function generarGrafico($miUnidadId, int $año): ?string
    {
        $cacheKey = 'grafico_html_' . ($miUnidadId ?? 'global') . '_' . $año . '_' . date('m');

        return Cache::remember($cacheKey, 600, function () use ($miUnidadId, $año) {
            $nombresMeses = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];

            $statsMensuales = Ticket::selectRaw('MONTH(created_at) as mes, estado_id, COUNT(*) as total')
                ->whereYear('created_at', $año)
                ->when($miUnidadId, fn($q) => $q->whereHas('categoria', fn($cat) => $cat->where('unidad_id', $miUnidadId)))
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
        });
    }

    private function calcularMetricasHistorial($user, $miUnidadId, int $año): array
    {
        $filterUnidad = fn($q) => $q->when($user->rol_id == 3 && $miUnidadId, function($sub) use ($miUnidadId) {
            $sub->whereHas('categoria', fn($cat) => $cat->where('unidad_id', $miUnidadId));
        });

        // Carga de trabajo del día
        $cargaTrabajo = Ticket::whereYear('created_at', $año)
            ->whereDate('created_at', Carbon::today())
            ->tap($filterUnidad)
            ->count();

        // Resueltos en las últimas 24 horas
        $resueltos24h = Ticket::whereIn('estado_id', self::ESTADOS_CERRADOS)
            ->where('fecha_cierre', '>=', Carbon::now()->subDay())
            ->tap($filterUnidad)
            ->count();

        // Métricas del mes actual para Tasa de Cierre
        $totalMes = Ticket::whereYear('created_at', $año)
            ->whereMonth('created_at', Carbon::now()->month)
            ->tap($filterUnidad)
            ->count();

        $cerradosMes = Ticket::whereYear('created_at', $año)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereIn('estado_id', self::ESTADOS_CERRADOS)
            ->tap($filterUnidad)
            ->count();

        $tasaCierre = $totalMes > 0 ? (int) round(($cerradosMes / $totalMes) * 100) : 0;

        return [$cargaTrabajo, $resueltos24h, $tasaCierre];
    }

    private function obtenerConteoPrioridades($miUnidadId): array
    {
        $prioridadesQuery = Ticket::selectRaw('prioridad_id, COUNT(*) as total')
            ->whereNotIn('estado_id', self::ESTADOS_CERRADOS)
            ->when($miUnidadId, fn($q) => $q->whereHas('categoria', fn($cat) => $cat->where('unidad_id', $miUnidadId)))
            ->groupBy('prioridad_id')
            ->pluck('total', 'prioridad_id');

        return [
            'critica' => (int) ($prioridadesQuery[1] ?? 0),
            'alta'    => (int) ($prioridadesQuery[2] ?? 0),
            'media'   => (int) ($prioridadesQuery[3] ?? 0),
            'baja'    => (int) ($prioridadesQuery[4] ?? 0),
        ];
    }

    private function renderizarVista(string $tipo, $ticketsResult, $miUnidadId): string
    {
        $tecnicos = [];
        if (in_array($tipo, ['asignar', 'mis_asignados']) && $miUnidadId) {
            $tecnicos = User::select('id', 'name')->where('unidad_id', $miUnidadId)->where('activo', true)->get();
        }

        return match ($tipo) {
            'dashboard'     => view('partials.filas_dashboard', ['todosLosTickets' => $ticketsResult])->render(),
            'usuario'       => view('partials.filas_usuario', ['todosLosTickets' => $ticketsResult])->render(),
            'mis_tickets'   => view('partials.filas_mis_tickets', ['misTickets' => $ticketsResult])->render(),
            'historial'     => view('partials.filas_historial', ['tickets' => $ticketsResult])->render(),
            'asignar'       => view('partials.filas_asignar', ['tickets' => $ticketsResult, 'tecnicos' => $tecnicos])->render(),
            'mis_asignados' => view('partials.filas_mis_asignados', ['tickets' => $ticketsResult, 'tecnicos' => $tecnicos])->render(),
            default => '',
        };
    }
}