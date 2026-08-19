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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ComentarioController extends Controller
{
    //------------AUTORIZACION---------------
    private function autorizarAccesoTicket(Ticket $ticket, User $user): void
    {
        $esStaff = $user->esStaffDelTicket($ticket);
        $esPropietario = $ticket->user_id === $user->id;
        $esTecnicoAsignado = $ticket->tecnico_id !== null && $ticket->tecnico_id === $user->id;

        if (!$esStaff && !$esPropietario && !$esTecnicoAsignado) {
            abort(403, 'No tienes permiso para acceder a este ticket.');
        }
    }

    //------------METODO PARA TRAER TODOS LOS COMENTARIOS DE LA BASE---------------
    public function obtenerComentarios($ticketId)
    {
        $ticket = Ticket::findOrFail($ticketId);
        /** @var User $user */
        $user = Auth::user();
        $this->autorizarAccesoTicket($ticket, $user);

        $query = Comentario::with('user')->where('ticket_id', $ticket->id);

        if (!$user->esStaffDelTicket($ticket)) {
            $query->where('es_privado', false);
        }

        //-----usamos select o transformamos directo sin sobrecargar Carbon en bucles masivos
        $comentarios = $query->oldest()->get()->map(function ($com) {
            return [
                'id' => $com->id,
                'user' => [
                    'name' => $com->user->name ?? 'Usuario Desconocido'
                ],
                'contenido' => $com->contenido,
                'es_privado' => $com->es_privado,
                'tiempo_legible' => $com->created_at ? $com->created_at->diffForHumans() : ''
            ];
        });

        return response()->json($comentarios);
    }

    //-------metodo para guardar comentarios
    public function store(Request $request, $ticketId)
    {
        $request->validate([
            'contenido'  => 'required|string',
            'es_privado' => 'nullable|boolean',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $ticketParaAutorizar = Ticket::findOrFail($ticketId);
        $this->autorizarAccesoTicket($ticketParaAutorizar, $user);

        //====candado contra Doble-Clic por usuario + contenido + ticket (Válido por 5 segundos)
        $cacheKey = 'comment_lock_' . $user->id . '_' . $ticketId . '_' . md5(trim($request->contenido));
        if (!Cache::add($cacheKey, true, 5)) {
            return response()->json([
                'success' => false,
                'message' => 'Tu comentario ya se está procesando. Evita presionar dos veces el botón.'
            ], 429);
        }

        try {
            //----lógica de notas internas (Privadas): solo puede marcarla como tal quien es staff DE ESTE ticket puntual
            $esPrivado = false;
            if ($user->esStaffDelTicket($ticketParaAutorizar)) {
                $esPrivado = $request->has('es_privado') ? filter_var($request->es_privado, FILTER_VALIDATE_BOOLEAN) : false;
            }

            //------transacción de BD + Lock Pesimista del Ticket
            $resultado = DB::transaction(function () use ($ticketId, $user, $request, $esPrivado) {
                //-----bloqueamos el registro del ticket para asegurar la coherencia de estado y asignación
                $ticket = Ticket::with(['user', 'tecnico', 'estado', 'categoria'])
                    ->where('id', $ticketId)
                    ->lockForUpdate()
                    ->firstOrFail();

                //-----si el ticket se cerró mientras el modal seguía abierto en el navegador (el formulario ahí no se entera del cambio), igual se rechaza aquí
                if (in_array($ticket->estado_id, [3, 4, 5])) {
                    return [
                        'error' => true,
                        'message' => 'No se puede comentar, este ticket ya fue resuelto o cerrado.',
                    ];
                }

                //------crear el comentario
                $comentario = Comentario::create([
                    'ticket_id'  => $ticket->id,
                    'user_id'    => $user->id,
                    'contenido'  => trim($request->contenido),
                    'es_privado' => $esPrivado,
                ]);

                return [
                    'error'      => false,
                    'comentario' => $comentario,
                    'ticket'     => $ticket,
                ];
            });

            if ($resultado['error']) {
                Cache::forget($cacheKey);
                return response()->json([
                    'success' => false,
                    'message' => $resultado['message'],
                ], 422);
            }

            $comentario = $resultado['comentario'];
            $ticket     = $resultado['ticket'];

            $comentario->load('user');
            $comentario->tiempo_legible = $comentario->created_at->diffForHumans();

            //-----broadcast WebSockets si no es privado. El comentario ya se guardó;
            if (!$esPrivado) {
                try {
                    broadcast(new ComentarioCreado($comentario))->toOthers();
                } catch (\Exception $e) {
                    Log::error("Fallo al emitir broadcast ComentarioCreado en ticket #{$ticketId}: " . $e->getMessage());
                }
            }

            //---lógica de Envíos de Correos (Fuera de la transacción de BD)
            try {
                $estaResuelto = (
                    $ticket->estado_id == 3 ||
                    ($ticket->estado && in_array(strtolower($ticket->estado->nombre_estado), ['resuelto', 'equivocado', 'no corresponde']))
                );

                if (!$esPrivado && !$estaResuelto) {
                    $ticketCodigo   = "#TK" . str_pad($ticket->id, 5, '0', STR_PAD_LEFT);
                    $asuntoContexto = $ticket->asunto ?? $ticket->descripcion ?? 'Sin descripción especificada';
                    $nombreUsuario  = $user->name;
                    $nombreUnidad   = $user->unidad?->nombre_unidad ?? 'Unidad no especificada';

                    // ---- COMENTARIO REALIZADO POR EL CLIENTE ----
                    if ($user->id == $ticket->user_id) {

                        //---------------con técnico asignado-----------------------
                        if ($ticket->tecnico && $ticket->tecnico->id != $user->id) {

                            Mail::to($ticket->tecnico->email)->queue(new NotificacionTicketMail(
                                "Novedad en Ticket {$ticketCodigo}",
                                "Nuevo Mensaje del Solicitante",
                                "El usuario {$nombreUsuario} ha agregado un nuevo comentario o información adicional a esta solicitud.",
                                $ticketCodigo,
                                $comentario->contenido,
                                false,
                                $asuntoContexto,
                                $nombreUsuario,
                                $nombreUnidad
                            ));
                            Log::info("Correo enviado al técnico asignado: " . $ticket->tecnico->email);
                        }
                        //---------------Sin técnico asignado -> Notificar a la Unidad---------------
                        elseif (!$ticket->tecnico && $ticket->categoria) {

                            $unidadId = $ticket->categoria->unidad_id;
                            //---se excluye al propio usuario (es quien comentó, dueño del ticket) y se limita a solo avisos operativos
                            $destinatariosUnidad = User::where('unidad_id', $unidadId)
                                ->where('activo', true)
                                ->where('id', '!=', $user->id)
                                ->whereHas('rol', fn($q) => $q->whereIn('nombre_rol', ['Admin', 'Gestor']))
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
                                Log::info("Ticket sin asignar. Notificación enviada a unidad: " . $unidadId);
                            }
                        }
                    }

                    // ---- COMENTARIO REALIZADO POR ADMIN O TÉCNICO ----
                    if ($ticket->user && $ticket->user->id != $user->id && !empty($ticket->user->email)) {

                        $subtitulo = $ticket->tecnico
                            ? "El técnico asignado ha registrado una respuesta a tu solicitud."
                            : "Se ha registrado una nueva respuesta a tu solicitud.";

                        Mail::to($ticket->user->email)->queue(new NotificacionTicketMail(
                            "Respuesta a tu Ticket {$ticketCodigo}",
                            "Respuesta a tu Solicitud",
                            $subtitulo,
                            $ticketCodigo,
                            $comentario->contenido,
                            false,
                            $asuntoContexto,
                            $nombreUsuario,
                            $nombreUnidad
                        ));
                        Log::info("Correo de respuesta enviado al cliente: " . $ticket->user->email);
                    }
                }
            } catch (\Exception $e) {
                Log::error("Error al enviar correos de comentario: " . $e->getMessage());
            }

            return response()->json(['success' => true, 'comentario' => $comentario]);
        } catch (\Exception $e) {
            //-----liberar candado en caso de fallo catastrófico
            Cache::forget($cacheKey);
            Log::error("Error procesando comentario en ticket #{$ticketId}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al intentar guardar el comentario.'
            ], 500);
        }
    }
}
