<?php

namespace App\Http\Controllers;

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

        $mensaje = 'Ticket marcado como resuelto el ' . $ticket->fecha_cierre->format('d/m/Y H:i');

        //---ENVIAR EL CORREO---
        try {
            Mail::to($ticket->user->email)->send(new TicketResueltoMail($ticket));
        } catch (\Exception $e) {
            Log::error("Error enviando correo de ticket resuelto: " . $e->getMessage());
        }

        $mensaje = 'Ticket marcado como resuelto el ' . $ticket->fecha_cierre->format('d/m/Y H:i');

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => $mensaje]);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $mensaje
            ]);
        }

        return redirect()->back()->with('sweet_success', $mensaje);
    }
}
