<?php

namespace App\Http\Controllers;

use App\Events\TicketActualizado;
use App\Mail\TicketResueltoMail;
use App\Models\Comentario;
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
        $ahora = Carbon::now();

        $estadoSla = 'vencido';
        if ($ticket->fecha_vencimiento_sla && $ahora->lessThanOrEqualTo($ticket->fecha_vencimiento_sla)) {
            $estadoSla = 'cumplido';
        }

        $ticket->update([
            'estado_id' => 3,
            'fecha_cierre' => $ahora,
            'tiempo_respuesta' => $ticket->created_at ? $ahora->diffInSeconds($ticket->created_at, true) : 0,
            'estado_sla' => $estadoSla,
        ]);


        broadcast(new TicketActualizado());

        $mensaje = 'Ticket marcado como resuelto el ' . $ticket->fecha_cierre->format('d/m/Y H:i');

        //---ENVIAR EL CORREO---
        try {
            $ultimoComentario = Comentario::where('ticket_id', $ticket->id)
                ->where('es_privado', false)
                ->latest()
                ->first();

            $comentarioTexto = $ultimoComentario ? $ultimoComentario->contenido : null;

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
        $ahora = Carbon::now();

        $ticket->update([
            'estado_id' => 4,
            'fecha_cierre' => $ahora,
            'tiempo_respuesta' => $ticket->created_at ? $ahora->diffInSeconds($ticket->created_at, true) : 0,
            'estado_sla' => 'no aplica',
        ]);

        broadcast(new TicketActualizado());

        $mensaje = 'Ticket marcado como cerrado el ' . $ticket->fecha_cierre->format('d/m/Y H:i');

        //---ENVIAR EL CORREO---
        try {
             $ultimoComentario = Comentario::where('ticket_id', $ticket->id)
                ->where('es_privado', false)
                ->latest()
                ->first();

            $comentarioTexto = $ultimoComentario ? $ultimoComentario->contenido : null;

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
        $ahora = Carbon::now();

        $ticket->update([
            'estado_id' => 5,
            'fecha_cierre' => $ahora,
            'tiempo_respuesta' => $ticket->created_at ? $ahora->diffInSeconds($ticket->created_at, true) : 0,
            'estado_sla' => 'no aplica',
        ]);

        broadcast(new TicketActualizado());

        $mensaje = 'Ticket marcado como cerrado el ' . $ticket->fecha_cierre->format('d/m/Y H:i');

        //---ENVIAR EL CORREO---
        try {
             $ultimoComentario = Comentario::where('ticket_id', $ticket->id)
                ->where('es_privado', false)
                ->latest()
                ->first();

            $comentarioTexto = $ultimoComentario ? $ultimoComentario->contenido : null;
            
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
