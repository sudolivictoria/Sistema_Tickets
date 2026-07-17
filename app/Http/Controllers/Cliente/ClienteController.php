<?php

namespace App\Http\Controllers\Cliente;

use App\Events\TicketActualizado;
use App\Http\Controllers\Controller;
use App\Mail\NuevaSolicitudUnidadMail;
use App\Mail\TicketCreadoMail;
use App\Models\Categoria;
use App\Models\CategoriaManual;
use App\Models\Manual;
use App\Models\Prioridad;
use App\Models\Ticket;
use App\Models\TipoSolicitud;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ClienteController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $estadosCerrados = [3, 4, 5];

        //----estadísticas de tickets activos del cliente (Históricos / Sin límite de año)
        $abiertos = Ticket::where('user_id', $userId)
            ->whereNull('tecnico_id')
            ->whereNotIn('estado_id', $estadosCerrados)
            ->count();

        $enProceso = Ticket::where('user_id', $userId)
            ->whereNotNull('tecnico_id')
            ->where('estado_id', 2)
            ->count();

        //----tickets resueltos del cliente (SINCRO CON API: Únicamente del MES Y AÑO ACTUAL)
        $resueltos = Ticket::where('user_id', $userId)
            ->whereIn('estado_id', $estadosCerrados)
            ->whereMonth('created_at', date('m'))
            ->whereYear('created_at', date('Y'))
            ->count();

        $todosLosTickets = Ticket::where('user_id', $userId)
            ->with(['categoria', 'tipo_solicitud', 'prioridad', 'estado', 'tecnico'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        //----Parámetros estáticos para modales de creación
        $categorias = Categoria::all();
        $prioridades = Prioridad::all();
        $tipos = TipoSolicitud::all();
        //$categoriasManuales = CategoriaManual::orderBy('nombre_categoria_manual')->get();

        return view('usuario.dashboard', compact('abiertos', 'enProceso', 'resueltos', 'todosLosTickets', 'categorias', 'prioridades', 'tipos'));
    }

    //---metodo para mostrar formulario de creacion de ticket
    public function create()
    {
        $categorias = Categoria::all();
        $tipos = TipoSolicitud::all();
        $prioridades = Prioridad::all();

        return view('usuario.crear-ticket', compact('categorias', 'tipos', 'prioridades'));
    }

    //---metodo para procesar el formulario de creacion de ticket
    //---metodo para crear ticket
    public function store(Request $request)
    {
        $userId = Auth::id();
        $checkSum = md5($userId . trim($request->asunto));
        $cacheKey = 'submit_lock_' . $checkSum;
        if (!Cache::add($cacheKey, true, 20)) {
            return redirect()->route('admin.crear-ticket')
                ->with('success', '¡Recibido! Tu solicitud ya se está procesando.');
        }
        //-----validacion datos
        $request->validate([
            'asunto' => 'required|string|min:5|max:50',
            'categoria_id' => 'required|exists:categorias,id',
            'tipo_solicitud_id' => 'required|exists:tipo_solicitudes,id',
            'descripcion' => 'required|string',
            'prioridad_id' => 'required|exists:prioridades,id',

        ]);

        //----SLA
        $categoria = Categoria::find($request->categoria_id);
        $unidadId = $categoria ? $categoria->unidad_id : null;

        $horasSla = 24;

        if ($unidadId) {
            $sla = DB::table('prioridad_unidad')
                ->where('unidad_id', $unidadId)
                ->where('prioridad_id', $request->prioridad_id)
                ->first();

            if ($sla && isset($sla->horas_sla)) {
                $horasSla = (int)$sla->horas_sla;
            }
        }
        $fechaVencimiento = Carbon::now()->addHours($horasSla);

        $rutaEvidencia = null;
        if ($request->hasFile('evidencia')) {
            $rutaEvidencia = $request->file('evidencia')->store('evidencias', 'public');
        }

        //--crear ticket
        $nuevoTicket = Ticket::create([
            'asunto' => $request->asunto,
            'descripcion' => $request->descripcion,
            'drive_link' => $rutaEvidencia,
            'categoria_id' => $request->categoria_id,
            'tipo_solicitud_id' => $request->tipo_solicitud_id,
            'user_id' => Auth::id() ?? 1, //----asignar el ticket al usuario autenticado
            'estado_id' => 1, //---abierto
            'prioridad_id' => $request->prioridad_id,
            'tecnico_id' => null, //---vacio inicial 
            'fecha_vencimiento_sla' => $fechaVencimiento,
            'estado-sla' => 'pendiente',

        ]);

        //---cargar relaciones para el correo
        $nuevoTicket->load(['user', 'categoria.unidad', 'prioridad', 'tipo_solicitud']);

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
                Mail::to($destinatario)->queue(new TicketCreadoMail($nuevoTicket));
                $mensajeFlash = '¡Ticket creado con éxito y correo enviado!';
            }
        } catch (\Exception $e) {
            //--guardar ticket aunque no se cree el correo
            Log::error("Fallo al enviar correo de Ticket #" . $nuevoTicket->id . ": " . $e->getMessage());
            $mensajeFlash = 'Ticket creado, pero no se pudo enviar el correo de confirmación.';
        }


        //--------notificacion a la unidad correspondiente
        try {
            //---identificar unidad por medio de la categoria del ticket
            $unidadId = $nuevoTicket->categoria->unidad_id;

            //---obtener emails de gestores de la unidad
            $destinatarios = User::where('unidad_id', $unidadId)
                ->pluck('email')
                ->toArray();

            if (!empty($destinatarios)) {
                //--bcc para enviar a todos los gestores sin mostrar los emails entre ellos
                Mail::bcc($destinatarios)->queue(new NuevaSolicitudUnidadMail($nuevoTicket));
            }
        } catch (\Exception $e) {
            Log::error("Error avisando a la unidad: " . $e->getMessage());
        }

        broadcast(new TicketActualizado());

        //--redireccionar con mensaje de exito o error en el correo
        return redirect()->route('usuario.crear-ticket')
            ->with('success', $mensajeFlash);
    }

    public function misTickets()
    {
        $misTickets = Ticket::where('user_id', Auth::id())
            ->with(['categoria', 'tipo_solicitud', 'prioridad', 'estado', 'tecnico'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('usuario.mis-tickets', compact('misTickets'));
    }

    public function recursos()
    {
        $categorias = CategoriaManual::orderBy('nombre_categoria_manual', 'asc')->get();
        $manuales = Manual::with('categoria')->latest()->get();
        return view('usuario.recursos', compact('categorias', 'manuales'));
    }
}
