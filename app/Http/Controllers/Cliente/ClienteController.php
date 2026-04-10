<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Manual;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClienteController extends Controller
{
    public function index()
    {


        $userId = Auth::id();

        //----estadisticas tickets del cliente autenticado
        $abiertos = Ticket::where('user_id', $userId)
            ->whereNull('tecnico_id')
            ->where('estado_id', '!=', 3) 
            ->count();

        $enProceso = Ticket::where('user_id', $userId)
            ->whereNotNull('tecnico_id')
            ->where('estado_id', '!=', 3)
            ->count();

        $resueltos = Ticket::where('user_id', $userId)
            ->where('estado_id', 3)
            ->count();


        $manuales = Manual::latest()->take(3)->get();

        //----tickets del cliente autenticado
        $todosLosTickets = Ticket::where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('cliente.dashboard', compact('abiertos', 'enProceso', 'resueltos', 'manuales', 'todosLosTickets'));
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
