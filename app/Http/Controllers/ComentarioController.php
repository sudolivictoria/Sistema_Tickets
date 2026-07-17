<?php

namespace App\Http\Controllers;

use App\Models\Comentario;
use App\Http\Controllers\Controller;
use App\Mail\NotificacionTicketMail;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class ComentarioController extends Controller
{
    /**
     * Obtiene comentarios filtrados por rol para el modal
     */
    public function obtenerComentarios($ticketId)
    {
        $ticket = Ticket::findOrFail($ticketId);
        /** @var User $user */
        $user = Auth::user();

        $query = Comentario::with('user')->where('ticket_id', $ticket->id);

        if (!$user->tieneRol('Admin') && !$user->tieneRol('Gestor')) {
            $query->where('es_privado', false);
        }

        $comentarios = $query->oldest()->get()->map(function ($com) {
            return [
                'user' => [
                    'name' => $com->user ? $com->user->name : 'Usuario Desconocido'
                ],
                'contenido' => $com->contenido,
                'es_privado' => $com->es_privado,
                'tiempo_legible' => $com->created_at->diffForHumans()
            ];
        });

        return response()->json($comentarios);
    }

    /**
     * Guarda el comentario y envía el correo correspondiente
     */
    public function store(Request $request, $ticketId)
    {
        $request->validate([
            'contenido' => 'required|string',
            'es_privado' => 'boolean',
        ]);

        // Si no hacemos esto, $ticket->tecnico->email da un error fatal "Property of non-object".
        $ticket = Ticket::with(['user', 'tecnico', 'estado', 'categoria'])->findOrFail($ticketId);

        /** @var User $user */
        $user = Auth::user();

        $esPrivado = false;
        if ($user && ($user->tieneRol('Admin') || $user->tieneRol('Gestor'))) {
            $esPrivado = $request->has('es_privado') ? filter_var($request->es_privado, FILTER_VALIDATE_BOOLEAN) : false;
        }

        $comentario = Comentario::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'contenido' => $request->contenido,
            'es_privado' => $esPrivado,
        ]);

      //********** LÓGICA DE CORREOS ADAPTADA A TU SISTEMA DE UNIDADES **************
        try {
            $estaResuelto = (
                $ticket->estado_id == 3 ||
                ($ticket->estado && in_array(strtolower($ticket->estado->nombre_estado), ['resuelto', 'equivocado', 'no corresponde']))
            );

            if (!$esPrivado && !$estaResuelto) {
                $ticketCodigo = "#TK" . str_pad($ticket->id, 5, '0', STR_PAD_LEFT);

                //--cliente agrega comentario
                if ($user->id == $ticket->user_id) {
                    
                    //---tiene un tecnico asignado
                    if ($ticket->tecnico && $ticket->tecnico->id != $user->id) {
                        
                        Mail::to($ticket->tecnico->email)->send(new NotificacionTicketMail(
                            "Actualización en Ticket {$ticketCodigo}",
                            "Nuevo Comentario",
                            "El usuario {$user->name} ha agregado información o dudas sobre el caso que tienes asignado.",
                            $ticketCodigo,
                            $comentario->contenido,
                            false
                        ));
                        logger("Correo enviado al técnico asignado: " . $ticket->tecnico->email);
                    } 
                    
                    //---tecnico no asignado
                    elseif (!$ticket->tecnico) {
                        
                        if ($ticket->categoria) {
                            $unidadId = $ticket->categoria->unidad_id;
                            $destinatariosUnidad = User::where('unidad_id', $unidadId)
                                ->where('activo', true)
                                ->pluck('email')
                                ->toArray();

                            if (!empty($destinatariosUnidad)) {
                                Mail::bcc($destinatariosUnidad)->queue(new NotificacionTicketMail(
                                    "Ticket sin asignar actualizado {$ticketCodigo}",
                                    "Comentario en Ticket Pendiente",
                                    "El cliente {$user->name} ha comentado en un ticket de tu unidad que aún está pendiente de asignación.",
                                    $ticketCodigo,
                                    $comentario->contenido,
                                    false
                                ));
                                logger("Ticket sin asignar. Notificación en cola enviada a la unidad ID: " . $unidadId);
                            }
                        } else {
                            logger("Advertencia: El ticket no tiene una categoría asociada para identificar la unidad.");
                        }
                    }
                } 
                
                //---admin para cliente
                else {
                    if ($ticket->user && $ticket->user->id != $user->id && !empty($ticket->user->email)) {
                        Mail::to($ticket->user->email)->send(new NotificacionTicketMail(
                            "Actualización de Ticket {$ticketCodigo} - Help Desk ISTU",
                            "Nuevo Comentario",
                            "Se ha agregado una respuesta de seguimiento a su solicitud.",
                            $ticketCodigo,
                            $comentario->contenido,
                            false
                        ));
                        logger("Correo enviado al cliente: " . $ticket->user->email);
                    }
                }
            }
        } catch (\Exception $e) {
            logger("Error al enviar correo en flujo de unidades: " . $e->getMessage());
        }

        return response()->json(['success' => true, 'comentario' => $comentario]);
    }
}
