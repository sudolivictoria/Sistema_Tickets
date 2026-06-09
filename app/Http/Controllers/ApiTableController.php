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
use Throwable;

/**
 * Controller ApiTableController
 * Gestiona las peticiones de auto-refresco en tiempo real (vía WebSockets) para las tablas,
 * contadores, métricas y gráficos de rendimiento según el rol del usuario autenticado.
 * Espeja la lógica exacta de AdminController, AdminUnidadController y ClienteController.
 */
class ApiTableController extends Controller
{
    private const TIPOS_VALIDOS       = ['dashboard', 'usuario', 'asignar', 'mis_tickets', 'mis_asignados', 'historial'];
    private const TIPOS_SOLO_CONTENIDO = ['recursos'];
    private const TIPOS_SOLO_STAFF    = ['asignar', 'historial', 'mis_asignados'];
    private const ESTADOS_CERRADOS    = [3, 4, 5];

    public function refresh(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'No autenticado.'], 401);
        }

        $tipo = (string) $request->input('tipo', 'dashboard');

        if (!in_array($tipo, self::TIPOS_VALIDOS, true) && !in_array($tipo, self::TIPOS_SOLO_CONTENIDO, true)) {
            return response()->json(['error' => 'Tipo de tabla no válido.'], 400);
        }

        if (in_array($tipo, self::TIPOS_SOLO_STAFF, true) && !in_array($user->rol_id, [1, 3])) {
            return response()->json(['error' => 'No autorizado.'], 403);
        }

        try {
            if (in_array($tipo, self::TIPOS_SOLO_CONTENIDO, true)) {
                return response()->json([
                    'html' => $this->renderizarVista($tipo, null, (int) $user->unidad_id),
                ]);
            }

            $estadoFiltro = (string) $request->query('estado', 'todos');
            $miUnidadId   = (int) $user->unidad_id;

            // =====================================================================
            // ---------------------------CONTADORES--------------------------------
            // =====================================================================
            $queryAbiertos  = Ticket::whereNull('tecnico_id')->whereNotIn('estado_id', self::ESTADOS_CERRADOS);
            $queryProceso   = Ticket::whereNotNull('tecnico_id')->where('estado_id', 2);

            $queryResueltos = Ticket::whereIn('estado_id', self::ESTADOS_CERRADOS)
                ->whereMonth('created_at', date('m'))
                ->whereYear('created_at', date('Y'));

            if ($miUnidadId && in_array($user->rol_id, [1, 3])) {
                // Admins y gestores: segmentar por unidad
                $filterUnidad = fn($q) => $q->where('unidad_id', $miUnidadId);
                $queryAbiertos->whereHas('categoria', $filterUnidad);
                $queryProceso->whereHas('categoria', $filterUnidad);
                $queryResueltos->whereHas('categoria', $filterUnidad);
            }

            if ($user->rol_id == 2) {
                // Cliente: solo sus propios tickets
                $queryAbiertos->where('user_id', $user->id);
                $queryProceso->where('user_id', $user->id);
                $queryResueltos->where('user_id', $user->id);
            }

            $contadores = [
                'abiertos'  => $queryAbiertos->count(),
                'proceso'   => $queryProceso->count(),
                'resueltos' => $queryResueltos->count(),
            ];

            $contadorMisAsignados = Ticket::where('tecnico_id', $user->id)
                ->where('estado_id', 2)
                ->count();

            // =====================================================================
            // -----------------------------TABLA PRINCIPAL-------------------------
            // =====================================================================
            $query = Ticket::with(['user', 'categoria', 'estado', 'tecnico', 'prioridad', 'tipo_solicitud']);
            $this->aplicarFiltrosBase($query, $user, $tipo, $miUnidadId, $estadoFiltro);

            $limit         = ($tipo === 'usuario') ? 5 : null;
            $ticketsResult = $limit
                ? $query->latest()->limit($limit)->get()
                : $query->latest()->get();

            $graficoHtml = in_array($user->rol_id, [1, 3])
                ? $this->generarGrafico($miUnidadId)
                : '';

            [$cargaTrabajo, $resueltos24h, $tasaCierre] = ($tipo === 'historial')
                ? $this->calcularMetricas($ticketsResult)
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
            Log::error('ApiTableController refresh error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Error interno del servidor.'], 500);
        }
    }

    /**
     * Aplica filtros base a la query principal.
     * FIX: recibe $estadoFiltro como parámetro para evitar que inyectarFiltroEstado
     * pise los filtros de dashboard que ya manejan estados internamente.
     */
    private function aplicarFiltrosBase($query, $user, string $tipo, int $miUnidadId, string $estadoFiltro): void
    {
        //-------CLIENTE O MIS TICKETS-----
        if ($tipo === 'mis_tickets' || ($user->rol_id == 2 && $tipo !== 'dashboard')) {
            $query->where('user_id', $user->id);
            return;
        }

        //********DASHBOARD************
        if ($tipo === 'dashboard') {
            if ($estadoFiltro === 'resuelto,equivocado,no corresponde' || $estadoFiltro === 'cerrado') {
                $query->whereIn('estado_id', self::ESTADOS_CERRADOS)
                    ->whereMonth('created_at', date('m'))
                    ->whereYear('created_at', date('Y'));
            } elseif ($estadoFiltro === 'abierto') {
                $query->whereNull('tecnico_id')
                    ->whereNotIn('estado_id', self::ESTADOS_CERRADOS);
            } elseif ($estadoFiltro === 'procesando') {
                $query->whereNotNull('tecnico_id')->where('estado_id', 2);
            } else {
                //--------------TODOS EXCLUYE CERRADO
                $query->whereNotIn('estado_id', self::ESTADOS_CERRADOS);
            }

            if ($user->rol_id == 2) {
                $query->where('user_id', $user->id);
            }

            if ($miUnidadId && in_array($user->rol_id, [1, 3])) {
                $query->whereHas('categoria', fn($q) => $q->where('unidad_id', $miUnidadId));
            }
            return;
        }

        //-------------ASIGNAR
        if ($tipo === 'asignar') {
            $query->whereNull('tecnico_id')
                ->whereNotIn('estado_id', self::ESTADOS_CERRADOS);
            if ($miUnidadId) {
                $query->whereHas('categoria', fn($q) => $q->where('unidad_id', $miUnidadId));
            }
            return;
        }

        //-----------MIS ASIGNADOS
        if ($tipo === 'mis_asignados') {
            $query->where('tecnico_id', $user->id)->where('estado_id', 2);
            if ($miUnidadId) {
                $query->whereHas('categoria', fn($q) => $q->where('unidad_id', $miUnidadId));
            }
            return;
        }

        //-------------HISTORIAL
        if ($tipo === 'historial') {
            $query->whereYear('created_at', date('Y'));
            //----Gestor solo ve su unidad
            if ($user->rol_id == 3 && $miUnidadId) {
                $query->whereHas('categoria', fn($q) => $q->where('unidad_id', $miUnidadId));
            }
            return;
        }
    }

    //---------GRAFICO
    private function generarGrafico(int $miUnidadId): string
    {
        try {
            $añoActual    = date('Y');
            $nombresMeses = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];
            $mesesGrafico = [];

            $queryGrafico = Ticket::selectRaw('MONTH(created_at) as mes, estado_id, COUNT(*) as total')
                ->whereYear('created_at', $añoActual);

            if ($miUnidadId) {
                $queryGrafico->whereHas('categoria', fn($q) => $q->where('unidad_id', $miUnidadId));
            }

            $statsMensuales = $queryGrafico->groupBy('mes', 'estado_id')->get();

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

        } catch (Throwable $e) {
            Log::error('Error generando gráfico: ' . $e->getMessage());
            return '<div class="text-slate-400 text-xs p-4 text-center">Error al actualizar el gráfico.</div>';
        }
    }

    //----------CALCULAR METRICAS
    private function calcularMetricas($tickets): array
    {
        $cargaTrabajo = $tickets->filter(fn($t) => Carbon::parse($t->created_at)->isToday())->count();

        $hace24Horas  = now()->subDay();
        $resueltos24h = $tickets->whereIn('estado_id', self::ESTADOS_CERRADOS)
            ->filter(fn($t) => $t->fecha_cierre && Carbon::parse($t->fecha_cierre)->gte($hace24Horas))
            ->count();

        $delMes     = $tickets->filter(fn($t) => Carbon::parse($t->created_at)->isCurrentMonth());
        $total      = $delMes->count();
        $cerrados   = $delMes->whereIn('estado_id', self::ESTADOS_CERRADOS)->count();
        $tasaCierre = $total > 0 ? (int) round(($cerrados / $total) * 100) : 0;

        return [$cargaTrabajo, $resueltos24h, $tasaCierre];
    }

    //---------RENDERIZAR VISTA
    private function renderizarVista(string $tipo, $ticketsResult, int $miUnidadId): string
    {
        return match ($tipo) {
            'dashboard'     => view('partials.filas_dashboard', ['todosLosTickets' => $ticketsResult])->render(),
            'usuario'       => view('partials.filas_usuario',   ['todosLosTickets' => $ticketsResult])->render(),
            'mis_tickets'   => view('partials.filas_mis_tickets', ['misTickets' => $ticketsResult])->render(),
            'historial'     => view('partials.filas_historial',   ['tickets'    => $ticketsResult])->render(),
            'recursos'      => view('partials.filas_recursos', ['manuales' => Manual::with('categoria')->latest()->get()])->render(),
            'asignar'       => view('partials.filas_asignar', [
                'tickets'  => $ticketsResult,
                'tecnicos' => User::where('unidad_id', $miUnidadId)->where('activo', true)->get(),
            ])->render(),
            // FIX: mis_asignados también recibe tecnicos — igual que AdminController::misAsignados()
            'mis_asignados' => view('partials.filas_mis_asignados', [
                'tickets'  => $ticketsResult,
                'tecnicos' => User::where('unidad_id', $miUnidadId)->where('activo', true)->get(),
            ])->render(),
            default => '',
        };
    }
}