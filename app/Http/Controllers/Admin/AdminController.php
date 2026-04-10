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
        $noAsignados = Ticket::whereNull('tecnico_id')
            ->where('estado_id', '!=', 3)
            ->count();


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
            ->paginate(25);


        //----manuales
        $manuales = Manual::latest()->take(3)->get();

        return view('admin.dashboard', compact('noAsignados', 'pendientes', 'resueltos', 'todosLosTickets', 'mesesGrafico', 'manuales'));
    }


    //---metodos para las paginas---

    public function asignarTickets()
    {
        return "Página de Asignar Tickets (En construcción)";
    }

    public function misAsignados()
    {
        return "Página de Mis Asignados (En construcción)";
    }

    public function gestionUsuarios()
    {
        return "Página de Gestión de Usuarios (En construcción)";
    }

    public function gestionRecursos()
    {
        return "Página de Gestión de Recursos (En construcción)";
    }

    public function create()
    {
        return "Formulario para crear Ticket (En construcción)";
    }

    public function misTickets()
    {
        return "Historial de mis tickets (En construcción)";
    }

    public function recursos()
    {
        return "Biblioteca de manuales (En construcción)";
    }
}
