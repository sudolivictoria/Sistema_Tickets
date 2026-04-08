<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

        //----ultimos tickets
        $ticketsRecientes = Ticket::with(['user', 'prioridad'])
                                    ->latest()
                                    ->take(5)
                                    ->get();

        return view('admin.dashboard', compact('noAsignados', 'pendientes', 'resueltos', 'ticketsRecientes'));
    }
}
