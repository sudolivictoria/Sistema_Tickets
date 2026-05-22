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
        } else {
            $query->whereHas('categoria', function ($q) use ($miUnidadId) {
                $q->where('unidad_id', $miUnidadId);
            });
            $statsQuery->whereHas('categoria', function ($q) use ($miUnidadId) {
                $q->where('unidad_id', $miUnidadId);
            });

            if ($tipo == 'asignar') {
                $query->where('estado_id', 1);
            }

            if ($tipo == 'mis_asignados') {
                $query->where('estado_id', 2)->where('tecnico_id', $user->id);
            }
        }

        if ($tipo == 'usuario') {
            $query->limit(5);
        }

        if($tipo == 'dashboard'){
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

            //---agrupa tickets por mes y estado
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
        }
        //--retornar JSON con el html
        return response()->json([
            'html' => $html,
            'contadores' => $contadores,
            'grafico' => $graficoHtml,
            'contadorAsignados' => (int)$contadorMisAsignados
        ]);
    }
}
