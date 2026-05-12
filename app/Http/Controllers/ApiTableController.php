<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
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
        $statsQuery = Ticket::query();
        $statsQuery->whereYear('created_at', date('Y'));

        if ($tipo == 'mis_tickets' || $user->rol_id == 2) {
            $query->where('user_id', $user->id);
            $statsQuery->where('user_id', $user->id);
        } else {
            $filtroUnidad = function ($q) use ($miUnidadId) {
                $q->where('unidad_id', $miUnidadId);
            };

            $query->whereHas('categoria', $filtroUnidad);
            $statsQuery->whereHas('categoria', $filtroUnidad);

            if ($tipo == 'asignar') {
                $query->where('estado_id', 1);
            }
        }


        $allTicketsForStats = $statsQuery->get();
        $contadores = [
            'abiertos'  => $allTicketsForStats->whereNull('tecnico_id')->where('estado_id', '!=', 3)->count(),
            'proceso'   => $allTicketsForStats->whereNotNull('tecnico_id')->where('estado_id', 2)->count(),
            'resueltos' => $allTicketsForStats->where('estado_id', 3)->count(),
        ];

        $mesesGrafico = [];
        $nombresMeses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

        $ticketsPorMes = $allTicketsForStats->groupBy(function ($ticket) {
            return \Carbon\Carbon::parse($ticket->created_at)->month;
        });

        for ($i = 1; $i <= 12; $i++) {
            $ticketsDelMes = $ticketsPorMes->get($i, collect());
            $total = $ticketsDelMes->count();
            $resueltos = $ticketsDelMes->where('estado_id', 3)->count();
            $pendientes = $total - $resueltos;

            $mesesGrafico[] = [
                'nombre' => $nombresMeses[$i - 1],
                'resueltos_pct' => $total > 0 ? round(($resueltos / $total) * 100) : 0,
                'pendientes_pct' => $total > 0 ? round(($pendientes / $total) * 100) : 0,
            ];
        }

        $graficoHtml = view('partials.grafico_rendimiento', compact('mesesGrafico'))->render();

        if ($tipo == 'usuario') {
            $query->limit(5);
        }

        $ticketsResult = $query->latest()->get();

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
        }
        //--retornar JSON con el html
        return response()->json([
            'html' => $html,
            'contadores' => $contadores,
            'grafico' => $graficoHtml
        ]);
    }
}
