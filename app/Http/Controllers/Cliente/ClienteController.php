<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Mail\TicketCreadoMail;
use App\Models\Categoria;
use App\Models\Manual;
use App\Models\Prioridad;
use App\Models\Ticket;
use App\Models\TipoSolicitud;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ClienteController extends Controller
{
    public function index()
    {


        $userId = Auth::id();

        //----estadisticas tickets del cliente autenticado
        $abiertos = Ticket::where('user_id', $userId)
            ->whereNull('tecnico_id')
            ->where('estado_id', '!=', 3)
            ->count();

        $enProceso = Ticket::where('user_id', $userId)
            ->whereNotNull('tecnico_id')
            ->where('estado_id', '!=', 3)
            ->count();

        $resueltos = Ticket::where('user_id', $userId)
            ->where('estado_id', 3)
            ->count();


        $manuales = Manual::latest()->take(3)->get();

        //----tickets del cliente autenticado
        $todosLosTickets = Ticket::where('user_id', Auth::id())
            ->latest()
            ->paginate(5);

        return view('cliente.dashboard', compact('abiertos', 'enProceso', 'resueltos', 'manuales', 'todosLosTickets'));
    }


    public function create()
    {
        $categorias = Categoria::all();
        $tipos = TipoSolicitud::all();
        $prioridades = Prioridad::all();

        return view('cliente.crear-ticket', compact('categorias', 'tipos', 'prioridades'));
    }



    public function store(Request $request)
    {
        //-----validacion datos
        $request->validate([
            'asunto' => 'required|string|min:5|max:50',
            'categoria_id' => 'required|exists:categorias,id',
            'tipo_solicitud_id' => 'required|exists:tipo_solicitudes,id',
            'descripcion' => 'required|string',
            'prioridad_id' => 'required|exists:prioridades,id',

        ], [
            'asunto.max' => 'El asunto es demasiado largo. Resume el problema en menos de 50 caracteres.',
            'asunto.min' => 'El asunto es demasiado corto. Debe tener al menos 5 caracteres.',
        ]);

        //--crear ticket
        $nuevoTicket = Ticket::create([
            'asunto' => $request->asunto,
            'descripcion' => $request->descripcion,
            'categoria_id' => $request->categoria_id,
            'tipo_solicitud_id' => $request->tipo_solicitud_id,
            'user_id' => Auth::id(), //----asignar el ticket al usuario autenticado
            'estado_id' => 1, //---abierto
            'prioridad_id' => $request->prioridad_id,
            'tecnico_id' => null, //---vacio inicial 
        ]);

        //---cargar relaciones para el correo
        $nuevoTicket->load(['user', 'categoria', 'prioridad', 'tipo_solicitud']);

        //---envio correo capturandolo del usuario autenticado
        try {
            //---obtenemos el email del usuario autenticado
            $usuario = Auth::user();
            $destinatario = $usuario->email;

            //---siempre envia el ticket, aunque falle el correo, para no perder la información del ticket creado
            if (empty($destinatario)) {
                Log::warning("Usuario {$usuario->id} no tiene email configurado. Ticket #" . $nuevoTicket->id);
                $mensajeFlash = 'Ticket creado, pero no se pudo enviar el correo (email no configurado).';
            } else {
                Mail::to($destinatario)->send(new TicketCreadoMail($nuevoTicket));
                $mensajeFlash = '¡Ticket creado con éxito y correo enviado!';
            }
        } catch (\Exception $e) {
            //--guardar ticket aunque no se cree el correo
            Log::error("Fallo al enviar correo de Ticket #" . $nuevoTicket->id . ": " . $e->getMessage());
            $mensajeFlash = 'Ticket creado, pero no se pudo enviar el correo de confirmación.';
        }

        //--redireccionar con mensaje de exito o error en el correo
        return redirect()->route('cliente.crear-ticket')
            ->with('success', $mensajeFlash);
    }

    public function misTickets()
    {
        return "Historial de mis tickets (En construcción)";
    }

    public function recursos()
    {
        return "Biblioteca de manuales (En construcción)";
    }
}
