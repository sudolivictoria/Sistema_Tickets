<?php

namespace App\Http\Controllers;

use App\Events\TicketActualizado;
use App\Mail\TicketResueltoMail;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TicketController extends Controller
{
    public function resolver(Request $request, $id)
    {
        $ticket = Ticket::with(['user', 'tecnico'])->findOrFail($id);

        $ticket->update([
            'estado_id' => 3,
            'fecha_cierre' => Carbon::now()
        ]);

        broadcast(new TicketActualizado());

        $mensaje = 'Ticket marcado como resuelto el ' . $ticket->fecha_cierre->format('d/m/Y H:i');

        //---ENVIAR EL CORREO---
        try {
            Mail::to($ticket->user->email)->queue(new TicketResueltoMail($ticket));
        } catch (\Exception $e) {
            Log::error("Error enviando correo de ticket resuelto: " . $e->getMessage());
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => $mensaje]);
        }

        
        $urlOrigen = request()->headers->get('referer');

         return redirect()->to($urlOrigen)->with('sweet_success', $mensaje);
    }

    public function equivocacion(Request $request, $id)
    {
        $ticket = Ticket::with(['user', 'tecnico'])->findOrFail($id);

        $ticket->update([
            'estado_id' => 4,
            'fecha_cierre' => Carbon::now()
        ]);

        broadcast(new TicketActualizado());

        $mensaje = 'Ticket marcado como cerrado el ' . $ticket->fecha_cierre->format('d/m/Y H:i');

        //---ENVIAR EL CORREO---
        try {
            Mail::to($ticket->user->email)->queue(new TicketResueltoMail($ticket));
        } catch (\Exception $e) {
            Log::error("Error enviando correo de ticket cerrado: " . $e->getMessage());
        }

        broadcast(new TicketActualizado());

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => $mensaje]);
        }

         $urlOrigen = request()->headers->get('referer');

         return redirect()->to($urlOrigen)->with('sweet_success', $mensaje);
    }


    public function nocorresponde(Request $request, $id)
    {
        $ticket = Ticket::with(['user', 'tecnico'])->findOrFail($id);

        $ticket->update([
            'estado_id' => 5,
            'fecha_cierre' => Carbon::now()
        ]);

        broadcast(new TicketActualizado());

        $mensaje = 'Ticket marcado como cerrado el ' . $ticket->fecha_cierre->format('d/m/Y H:i');

        //---ENVIAR EL CORREO---
        try {
            Mail::to($ticket->user->email)->queue(new TicketResueltoMail($ticket));
        } catch (\Exception $e) {
            Log::error("Error enviando correo de ticket cerrado: " . $e->getMessage());
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => $mensaje]);
        }

         $urlOrigen = request()->headers->get('referer');

         return redirect()->to($urlOrigen)->with('sweet_success', $mensaje);
    }
}
