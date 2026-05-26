<?php

namespace App\Http\Controllers;

use App\Models\Manual;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiTableController extends Controller
{
    public function refresh(Request $request)
    {
        $tipo = $request->query('tipo');
        $user = Auth::user();
        $miUnidadId = $user->unidad_id;
        $query = Ticket::with(['user.unidad', 'estado', 'prioridad', 'tecnico', 'tipo_solicitud', 'categoria']);
        $statsQuery = Ticket::whereYear('created_at', date('Y'));
        $estadosCerrados = [3, 4, 5];

        if ($tipo == 'mis_tickets' || $user->rol_id == 2) {
            $query->where('user_id', $user->id);
            $statsQuery->where('user_id', $user->id);
        } elseif ($tipo == 'mis_asignados') {
            $query->where('tecnico_id', $user->id);
            $statsQuery->where('tecnico_id', $user->id);
        } else {
            //---datos del historial
            if ($tipo == 'historial') {
                if ($user->rol_id != 1) {
                    $query->whereHas('categoria', function ($q) use ($miUnidadId) {
                        $q->where('unidad_id', $miUnidadId);
                    });
                    $statsQuery->whereHas('categoria', function ($q) use ($miUnidadId) {
                        $q->where('unidad_id', $miUnidadId);
                    });
                }
            } else {
                $query->whereHas('categoria', function ($q) use ($miUnidadId) {
                    $q->where('unidad_id', $miUnidadId);
                });
                $statsQuery->whereHas('categoria', function ($q) use ($miUnidadId) {
                    $q->where('unidad_id', $miUnidadId);
                });
            }
            if ($tipo == 'asignar') {
                $query->where('estado_id', 1);
            }
        }
        if ($tipo == 'usuario') {
            $query->limit(5);
        }
        if ($tipo == 'dashboard') {
            $inicioMes = Carbon::now()->startOfMonth();
            $finMes = Carbon::now()->endOfMonth();
            $query->whereBetween('created_at', [$inicioMes, $finMes]);
        }
        $ticketsResult = $query->latest()->get();
        $allTicketsForStats = $statsQuery->get();
        $contadores = [
            'abiertos'  => $allTicketsForStats->whereNull('tecnico_id')->whereNotIn('estado_id', $estadosCerrados)->count(),
            'proceso'   => $allTicketsForStats->whereNotNull('tecnico_id')->where('estado_id', 2)->count(),
            'resueltos' => $allTicketsForStats->whereIn('estado_id', $estadosCerrados)->count(),
        ];

        $graficoHtml = null;

        if ($user->rol_id != 2) {

            $añoActual = date('Y');
            $nombresMeses = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];
            $mesesGrafico = [];

            //---agrupa tickets por mes y estado (mantiene tu comportamiento original para el gráfico)
            $statsMensuales = Ticket::selectRaw('MONTH(created_at) as mes, estado_id, COUNT(*) as total')
                ->whereYear('created_at', $añoActual)
                ->whereHas('categoria', function ($q) use ($miUnidadId) {
                    $q->where('unidad_id', $miUnidadId);
                })
                ->groupBy('mes', 'estado_id')
                ->get();

            for ($i = 1; $i <= 12; $i++) {
                $res = $statsMensuales->where('mes', $i)->whereIn('estado_id', $estadosCerrados)->sum('total');
                $pen = $statsMensuales->where('mes', $i)->whereNotIn('estado_id', $estadosCerrados)->sum('total');
                $total = $res + $pen;

                $mesesGrafico[] = [
                    'nombre' => $nombresMeses[$i - 1],
                    'resueltos_pct' => $total > 0 ? round(($res / $total) * 100) : 0,
                    'pendientes_pct' => $total > 0 ? round(($pen / $total) * 100) : 0,
                    'total' => $total
                ];
            }

            $graficoHtml = view('partials.grafico_rendimiento', compact('mesesGrafico'))->render();
        }

        $contadorMisAsignados = Ticket::where('tecnico_id', Auth::id())
            ->where('estado_id', 2)
            ->count();


        //--metricas tarjetas superiores
        $cargaTrabajo = 0;
        $resueltos24h = 0;
        $tasaCierre = 0;

        if ($tipo == 'historial') {
            $metricsQuery = Ticket::with(['user', 'categoria', 'estado', 'tecnico'])
                ->whereYear('created_at', date('Y'));

            //--gestor calcula por unidad
            if ($user->rol_id != 1) {
                $metricsQuery->whereHas('categoria', function ($q) use ($miUnidadId) {
                    $q->where('unidad_id', $miUnidadId);
                });
            }
            //---el admin lo hace de manera global

            $tickets = $metricsQuery->latest()->get();

            $cargaTrabajo = $tickets->filter(function ($ticket) {
                return Carbon::parse($ticket->created_at)->isToday();
            })->count();

            $resueltos24h = $tickets->whereIn('estado_id', [3, 4, 5])
                ->filter(function ($ticket) {
                    return $ticket->fecha_cierre && Carbon::parse($ticket->fecha_cierre)->gte(now()->subDay());
                })
                ->count();

            //-----tasa cierre mensual
            $ticketsDelMes = $tickets->filter(function ($ticket) {
                return Carbon::parse($ticket->created_at)->isCurrentMonth();
            });

            $totalTickets = $ticketsDelMes->count();
            $cerradosTickets = $ticketsDelMes->whereIn('estado_id', [3, 4, 5])->count();
            $tasaCierre = $totalTickets > 0 ? round(($cerradosTickets / $totalTickets) * 100) : 0;
        }


        //-----html segun el tipo de tabla que se refresca---
        $html = '';
        switch ($tipo) {
            case 'dashboard':
                $html = view('partials.filas_dashboard', ['todosLosTickets' => $ticketsResult])->render();
                break;

            case 'usuario':
                $html = view('partials.filas_usuario', ['todosLosTickets' => $ticketsResult])->render();
                break;

            case 'asignar':
                $tecnicos = User::where('unidad_id', $miUnidadId)->get();
                $html = view('partials.filas_asignar', ['tickets' => $ticketsResult, 'tecnicos' => $tecnicos])->render();
                break;

            case 'mis_tickets':
                $html = view('partials.filas_mis_tickets', ['misTickets' => $ticketsResult])->render();
                break;

            case 'recursos':
                $manuales = Manual::with('categoria')->latest()->get();
                $html = view('partials.filas_recursos', compact('manuales'))->render();
                break;

            case 'mis_asignados':
                $tecnicos = User::where('unidad_id', $miUnidadId)->where('activo', true)->get();
                $html = view('partials.filas_mis_asignados', [
                    'tickets' => $ticketsResult,
                    'tecnicos' => $tecnicos
                ])->render();
                break;

            case 'historial':
                $html = view('partials.filas_historial', ['tickets' => $ticketsResult])->render();
                break;
        }

        //--retornar JSON con todas las variables necesarias
        return response()->json([
            'html' => $html,
            'contadores' => $contadores,
            'grafico' => $graficoHtml,
            'contadorAsignados' => (int)$contadorMisAsignados,
            'cargaTrabajo' => $cargaTrabajo,
            'resueltos24h' => $resueltos24h,
            'tasaCierre' => $tasaCierre
        ]);
    }
}
