<?php

namespace App\Http\Controllers;

use App\Events\ComentarioCreado;
use App\Events\TicketActualizado;
use App\Mail\TicketResueltoMail;
use App\Models\Comentario;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TicketController extends Controller
{
    //----------- 1. RESOLVER TICKET (Estado ID: 3) ----------------------
    public function resolver(Request $request, $id)
    {
        return $this->procesarCierreTicket($request, $id, 3, function ($ticket, $ahora) {
            if ($ticket->fecha_vencimiento_sla && $ahora->lessThanOrEqualTo($ticket->fecha_vencimiento_sla)) {
                return 'cumplido';
            }
            return 'vencido';
        });
    }

    //----------- 2. EQUIVOCACION (Estado ID: 4) -------------------------
    public function equivocacion(Request $request, $id)
    {
        return $this->procesarCierreTicket($request, $id, 4, function () {
            return 'no aplica';
        });
    }

    //----------- 3. NO CORRESPONDE (Estado ID: 5) -----------------------
    public function nocorresponde(Request $request, $id)
    {
        return $this->procesarCierreTicket($request, $id, 5, function () {
            return 'no aplica';
        });
    }

    /**
     * Helper privado para procesar el cierre del ticket de forma atómica y concurrente
     */
    private function procesarCierreTicket(Request $request, $id, int $nuevoEstadoId, callable $determinarEstadoSla)
    {
        try {
            $resultado = DB::transaction(function () use ($request, $id, $nuevoEstadoId, $determinarEstadoSla) {
                // Bloqueamos la fila del ticket en BD
                $ticket = Ticket::where('id', $id)->lockForUpdate()->firstOrFail();

                // Validación concurrente: Verificar si otro usuario ya cambió el estado a cerrado
                if (in_array($ticket->estado_id, [3, 4, 5])) {
                    return [
                        'error' => true,
                        'message' => '¡Operación rechazada! Este ticket ya ha sido cerrado o resuelto por otro usuario.'
                    ];
                }

                $ahora = Carbon::now();
                $estadoSla = $determinarEstadoSla($ticket, $ahora);

                $ticket->update([
                    'estado_id'        => $nuevoEstadoId,
                    'fecha_cierre'     => $ahora,
                    'tiempo_respuesta' => $ticket->created_at ? $ahora->diffInSeconds($ticket->created_at, true) : 0,
                    'estado_sla'       => $estadoSla,
                ]);

                $comentarioCierreTexto = null;

                // Guardar comentario de cierre si fue ingresado
                if ($request->filled('comentario_cierre')) {
                    $textoFormateado = '[Cierre]: ' . trim($request->comentario_cierre);
                    $comentario = Comentario::create([
                        'ticket_id'  => $ticket->id,
                        'user_id'    => Auth::id() ?? $ticket->tecnico_id,
                        'contenido'  => $textoFormateado,
                        'es_privado' => false,
                    ]);

                    $comentarioCierreTexto = $textoFormateado;
                    broadcast(new ComentarioCreado($comentario))->toOthers();
                }

                return [
                    'error'           => false,
                    'ticket'          => $ticket,
                    'comentarioTexto' => $comentarioCierreTexto,
                    'fechaCierre'     => $ahora->format('d/m/Y H:i')
                ];
            });

            if ($resultado['error']) {
                return $request->ajax()
                    ? response()->json(['success' => false, 'message' => $resultado['message']], 422)
                    : back()->with('sweet_error', $resultado['message']);
            }

            $ticket = $resultado['ticket'];
            $mensaje = 'Ticket marcado como cerrado el ' . $resultado['fechaCierre'];

            broadcast(new TicketActualizado());

            // Envío de correo en segundo plano
            try {
                if ($ticket->user?->email) {
                    Mail::to($ticket->user->email)->queue(new TicketResueltoMail($ticket, $resultado['comentarioTexto']));
                }
            } catch (\Exception $e) {
                Log::error("Error enviando correo de cierre en Ticket #{$id}: " . $e->getMessage());
            }

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => $mensaje]);
            }

            $urlOrigen = request()->headers->get('referer');
            return redirect()->to($urlOrigen)->with('sweet_success', $mensaje);

        } catch (\Exception $e) {
            Log::error("Error de concurrencia/procesamiento al cerrar ticket #{$id}: " . $e->getMessage());
            $err = 'No se pudo procesar la solicitud debido a un conflicto de sistema.';
            
            return $request->ajax() 
                ? response()->json(['success' => false, 'message' => $err], 500) 
                : back()->with('sweet_error', $err);
        }
    }
}