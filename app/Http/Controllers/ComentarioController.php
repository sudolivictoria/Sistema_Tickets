<?php

namespace App\Http\Controllers;

use App\Events\ComentarioCreado;
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

    public function store(Request $request, $ticketId)
    {
        $request->validate([
            'contenido' => 'required|string',
            'es_privado' => 'boolean',
        ]);

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

        broadcast(new ComentarioCreado($comentario))->toOthers();

        //********** LÓGICA DE CORREOS **************
        try {
            $estaResuelto = (
                $ticket->estado_id == 3 ||
                ($ticket->estado && in_array(strtolower($ticket->estado->nombre_estado), ['resuelto', 'equivocado', 'no corresponde']))
            );

            if (!$esPrivado && !$estaResuelto) {
                $ticketCodigo = "#TK" . str_pad($ticket->id, 5, '0', STR_PAD_LEFT);
                $asuntoContexto = $ticket->asunto ?? $ticket->descripcion ?? 'Sin descripción especificada';
                $nombreUsuario = $user->name;
                $nombreUnidad = $user->unidad?->nombre_unidad ?? 'Unidad no especificada';

                //**************************COMMENT CLIENTE**************************************/
                if ($user->id == $ticket->user_id) {

                    //---tiene un tecnico asignado
                    if ($ticket->tecnico && $ticket->tecnico->id != $user->id) {

                        Mail::to($ticket->tecnico->email)->queue(new NotificacionTicketMail(
                            "Novedad en Ticket {$ticketCodigo}",
                            "Nuevo Mensaje del Solitante",
                            "El usuario {$nombreUsuario} ha agregado un nuevo comentario o información adicional a esta solicitud.",
                            $ticketCodigo,
                            $comentario->contenido,
                            false,
                            $asuntoContexto,
                            $nombreUsuario,
                            $nombreUnidad
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
                                    "Atención Requerida! nuevo comentario en Ticket #{$ticketCodigo} (Sin Asignar)",
                                    "Ticket Pendiente con Actividad",
                                    "El solicitante ha comentado en una solicitud pendiente de atención asignada a tu área.",
                                    $ticketCodigo,
                                    $comentario->contenido,
                                    false,
                                    $asuntoContexto,
                                    $nombreUsuario,
                                    $nombreUnidad
                                ));
                                logger("Ticket sin asignar. Notificación enviada a unidad: " . $unidadId);
                            }
                        }
                    }
                }

                //**********************COMMENT ADMIN O TECNICO********************************/
                if ($ticket->user && $ticket->user->id != $user->id && !empty($ticket->user->email)) {

                    //------------depende si tiene un tecnico asignado
                    if ($ticket->tecnico) {
                        Mail::to($ticket->user->email)->queue(new NotificacionTicketMail(
                            "Respuesta a tu Ticket {$ticketCodigo}",
                            "Respuesta a tu Solicitud",
                            "El técnico asignado ha registrado una respuesta a tu ticket.",
                            $ticketCodigo,
                            $comentario->contenido,
                            false,
                            $asuntoContexto,
                            $nombreUsuario,
                            $nombreUnidad
                        ));
                    } else {
                        Mail::to($ticket->user->email)->queue(new NotificacionTicketMail(
                            "Respuesta a tu Ticket {$ticketCodigo}",
                            "Respuesta a tu Solicitud",
                            "Se ha registrado una nueva respuesta a tu solicitud.",
                            $ticketCodigo,
                            $comentario->contenido,
                            false,
                            $asuntoContexto,
                            $nombreUsuario,
                            $nombreUnidad
                        ));
                    }
                    logger("Correo de respuesta enviado al cliente: " . $ticket->user->email);
                }
            }
        } catch (\Exception $e) {
            logger("Error al enviar correo: " . $e->getMessage());
        }

        return response()->json(['success' => true, 'comentario' => $comentario]);
    }
}
