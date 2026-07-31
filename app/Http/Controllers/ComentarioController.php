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
    //------------METODO PARA TRAER TODOS LOS COMENTARIOS DE LA BASE---------------
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

    //-------metodo para guardar comentarios
    public function store(Request $request, $ticketId)
    {
        $request->validate([
            'contenido'  => 'required|string',
            'es_privado' => 'nullable|boolean',
        ]);

        /** @var User $user */
        $user = Auth::user();

        // 1. Candado contra Doble-Clic por usuario + contenido + ticket (Válido por 5 segundos)
        $cacheKey = 'comment_lock_' . $user->id . '_' . $ticketId . '_' . md5(trim($request->contenido));
        if (!Cache::add($cacheKey, true, 5)) {
            return response()->json([
                'success' => false, 
                'message' => 'Tu comentario ya se está procesando. Evita presionar dos veces el botón.'
            ], 429);
        }

        try {
            // Lógica de notas internas (Privadas)
            $esPrivado = false;
            if ($user && ($user->tieneRol('Admin') || $user->tieneRol('Gestor'))) {
                $esPrivado = $request->has('es_privado') ? filter_var($request->es_privado, FILTER_VALIDATE_BOOLEAN) : false;
            }

            // 2. Transacción de BD + Lock Pesimista del Ticket
            $resultado = DB::transaction(function () use ($ticketId, $user, $request, $esPrivado) {
                // Bloqueamos el registro del ticket para asegurar la coherencia de estado y asignación
                $ticket = Ticket::with(['user', 'tecnico', 'estado', 'categoria'])
                    ->where('id', $ticketId)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Crear el comentario
                $comentario = Comentario::create([
                    'ticket_id'  => $ticket->id,
                    'user_id'    => $user->id,
                    'contenido'  => trim($request->contenido),
                    'es_privado' => $esPrivado,
                ]);

                return [
                    'comentario' => $comentario,
                    'ticket'     => $ticket,
                ];
            });

            $comentario = $resultado['comentario'];
            $ticket     = $resultado['ticket'];

            $comentario->load('user');
            $comentario->tiempo_legible = $comentario->created_at->diffForHumans();

            // Broadcast WebSockets si no es privado
            if (!$esPrivado) {
                broadcast(new ComentarioCreado($comentario))->toOthers();
            }

            // 3. Lógica de Envíos de Correos (Fuera de la transacción de BD)
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

                        // Con técnico asignado
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
                        // Sin técnico asignado -> Notificar a la Unidad
                        elseif (!$ticket->tecnico && $ticket->categoria) {

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
            // Liberar candado en caso de fallo catastrófico
            Cache::forget($cacheKey);
            Log::error("Error procesando comentario en ticket #{$ticketId}: " . $e->getMessage());

            return response()->json([
                'success' => false, 
                'message' => 'Ocurrió un error al intentar guardar el comentario.'
            ], 500);
        }
    }
}