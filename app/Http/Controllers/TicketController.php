<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function resolver(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        $ticket->estado_id = 3; 
        
        //---carbon para capturar el momento exacto de cierre del ticket---//
        $ticket->fecha_cierre = Carbon::now(); 

        $ticket->estado_id = 3;
        
        $ticket->save();

        return redirect()->back()->with('success', 'Ticket marcado como resuelto el ' . $ticket->fecha_cierre->format('d/m/Y H:i'));
    }
}
