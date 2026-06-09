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

        // Evita el bloqueo de peticiones síncronas en Laravel sin romper la sesión
        if (session()->id()) {
            session()->writeClose();
        }

        // Captura el tipo desde el POST (JSON enviado por api.js)
        $tipo = (string) $request->input('tipo', 'dashboard');
        $miUnidadId = (int) $user->unidad_id;
        $estadosCerrados = self::ESTADOS_CERRADOS;

        if (!in_array($tipo, self::TIPOS_VALIDOS) && !in_array($tipo, self::TIPOS_SOLO_CONTENIDO)) {
            return response()->json(['error' => 'Tipo de consulta no válido.'], 400);
        }

        try {
            //--- 1. FILTRADO Y RENDERIZACIÓN DE TABLAS SEGÚN REGLAS DE ROLES
            $htmlContenido = '';
            if (!in_array($tipo, self::TIPOS_SOLO_CONTENIDO)) {
                $query = Ticket::with(['user', 'categoria', 'estado', 'tecnico']);

                // REGLA CLIENTE (Rol 3): Solo ve sus propios tickets en cualquier lado
                if ($user->rol_id == 3) {
                    $query->where('user_id', $user->id);
                }
                // REGLA ADMIN UNIDAD (Rol 2): Ve estrictamente su unidad en TODO
                elseif ($user->rol_id == 2) {
                    if ($miUnidadId > 0) {
                        $query->whereHas('categoria', function ($q) use ($miUnidadId) {
                            $q->where('unidad_id', $miUnidadId);
                        });
                    }
                }
                // REGLA ADMIN GENERAL (Rol 1): SOLO SU UNIDAD, EXCEPTO HISTORIAL QUE ES GLOBAL
                elseif ($user->rol_id == 1) {
                    // Si el tipo es HISTORIAL, no se le añade ningún filtro (es GLOBAL)
                    // Si es cualquier otro tipo (dashboard, asignar, etc.), se filtra por su unidad
                    if ($tipo !== 'historial' && $miUnidadId > 0) {
                        $query->whereHas('categoria', function ($q) use ($miUnidadId) {
                            $q->where('unidad_id', $miUnidadId);
                        });
                    }
                }

                $ticketsResult = $query->latest()->get();
                $htmlContenido = $this->renderizarVista($tipo, $ticketsResult, $miUnidadId);
            }

            //--- 2. CÁLCULO DE CONTADORES EN TIEMPO REAL
            $qAbiertos = Ticket::whereNull('tecnico_id')->whereNotIn('estado_id', $estadosCerrados);
            $qProceso  = Ticket::whereNotNull('tecnico_id')->where('estado_id', 2);
            $qResueltos = Ticket::whereIn('estado_id', $estadosCerrados)
                ->whereMonth('created_at', date('m'))
                ->whereYear('created_at', date('Y'));

            // Aplicar restricciones a los contadores superiores basados en el Rol
            if ($user->rol_id == 3) {
                // Cliente: Contadores de lo suyo
                $userId = $user->id;
                $qAbiertos->where('user_id', $userId);
                $qProceso->where('user_id', $userId);
                $qResueltos->where('user_id', $userId);

                $ticketsContadores = Ticket::where('user_id', $userId)->whereYear('created_at', date('Y'))->get();
            } elseif ($user->rol_id == 2) {
                // Admin de Unidad: Contadores de su unidad
                $filtroUnidad = function ($q) use ($miUnidadId) {
                    $q->where('unidad_id', $miUnidadId);
                };
                $qAbiertos->whereHas('categoria', $filtroUnidad);
                $qProceso->whereHas('categoria', $filtroUnidad);
                $qResueltos->whereHas('categoria', $filtroUnidad);

                $ticketsContadores = Ticket::whereYear('created_at', date('Y'))->whereHas('categoria', $filtroUnidad)->get();
            } else {
                // Admin General (Rol 1): Como está viendo el "dashboard" o pestañas normales, 
                // sus contadores deben ser de SU UNIDAD (siguiendo tu regla de negocio).
                if ($miUnidadId > 0) {
                    $filtroUnidad = function ($q) use ($miUnidadId) {
                        $q->where('unidad_id', $miUnidadId);
                    };
                    $qAbiertos->whereHas('categoria', $filtroUnidad);
                    $qProceso->whereHas('categoria', $filtroUnidad);
                    $qResueltos->whereHas('categoria', $filtroUnidad);

                    $ticketsContadores = Ticket::whereYear('created_at', date('Y'))->whereHas('categoria', $filtroUnidad)->get();
                } else {
                    $ticketsContadores = Ticket::whereYear('created_at', date('Y'))->get();
                }
            }

            // Empaquetado final para los selectores de api.js
            $contadores = [
                'abiertos'   => $qAbiertos->count(),
                'proceso'    => $qProceso->count(),
                'resueltos'  => $qResueltos->count(),
                'asignados'  => Ticket::where('tecnico_id', $user->id)->whereNotIn('estado_id', $estadosCerrados)->count(),
            ];

            //--- 3. KPIs MÈTRICAS
            [$cargaTrabajo, $resueltos24h, $tasaCierre] = $this->calcularMetricas($ticketsContadores);

            return response()->json([
                'html'         => $htmlContenido,
                'contadores'   => $contadores,
                'kpis'         => [
                    'carga'     => $cargaTrabajo,
                    'resueltos' => $resueltos24h,
                    'tasa'      => $tasaCierre
                ]
            ]);
        } catch (Throwable $e) {
            Log::error("Fallo crítico en Auto-Refresco [Tipo: $tipo]: " . $e->getMessage());
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
