<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Manual;
use App\Models\Ticket;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        //----tickets sin asignar
        $noAsignados = Ticket::whereNull('tecnico_id')->count();

        //-----tickets no terminados (estado_id != 3, asumiendo que 3 es resuelto)
        $pendientes = Ticket::whereNotNull('tecnico_id')
            ->where('estado_id', '!=', 3)
            ->count();

        //----tickets resueltos
        $resueltos = Ticket::where('estado_id', 3)->count();


        //----estadísticas mensuales
        $añoActual = date('Y');
        $nombresMeses = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];
        $mesesGrafico = [];


        $statsMensuales = Ticket::selectRaw('MONTH(created_at) as mes, estado_id, COUNT(*) as total')
            ->whereYear('created_at', $añoActual)
            ->groupBy('mes', 'estado_id')
            ->get();

        for ($i = 1; $i <= 12; $i++) {
            $res = $statsMensuales->where('mes', $i)->where('estado_id', 3)->first()->total ?? 0; //--resuelto tiene que ser el 3
            $pen = $statsMensuales->where('mes', $i)->where('estado_id', '!=', 3)->first()->total ?? 0;

            $total = $res + $pen;

            $mesesGrafico[] = [
                'nombre' => $nombresMeses[$i - 1],
                'resueltos_pct' => $total > 0 ? ($res / $total) * 100 : 0,
                'pendientes_pct' => $total > 0 ? ($pen / $total) * 100 : 0,
                'total' => $total
            ];
        }


        //----ultimos tickets
        $todosLosTickets = Ticket::with(['user', 'prioridad'])
            ->latest()
            ->paginate(15);


        $manuales = Manual::latest()->take(3)->get();

        return view('admin.dashboard', compact('noAsignados', 'pendientes', 'resueltos', 'todosLosTickets', 'mesesGrafico', 'manuales'));
    }
}
