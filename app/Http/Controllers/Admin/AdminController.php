<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\NuevaSolicitudUnidadMail;
use App\Mail\TicketCreadoMail;
use App\Models\Categoria;
use App\Models\CategoriaManual;
use App\Models\Estado;
use App\Models\Manual;
use App\Models\Prioridad;
use App\Models\Ticket;
use App\Models\TipoSolicitud;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function index()
    {
        //--unidad del admin autenticado
        $miUnidadId = Auth::user()->unidad_id;
        //---estados cerrados
        $estadosCerrados = [3, 4, 5];

        //--tickets asignados por unidad del admin autenticado
        $noAsignados = Ticket::whereYear('created_at', date('Y'))
            ->whereNull('tecnico_id')
            ->whereNotIn('estado_id', $estadosCerrados)
            ->whereHas('categoria', fn($q) => $q->where('unidad_id', $miUnidadId))
            ->count();

        //--tickets pendientes por unidad del admin autenticado
        $pendientes = Ticket::whereYear('created_at', date('Y'))
            ->whereNotNull('tecnico_id')
            ->whereNotIn('estado_id', $estadosCerrados)
            ->whereHas('categoria', fn($q) => $q->where('unidad_id', $miUnidadId))
            ->count();

        //--tickets resueltos por unidad del admin autenticado
        $resueltos = Ticket::whereYear('created_at', date('Y'))
            ->whereIn('estado_id', $estadosCerrados)
            ->whereHas('categoria', fn($q) => $q->where('unidad_id', $miUnidadId))
            ->count();

        //---limitar a solo mostrar los tickets del mes
        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();

        //--tickets recientes por unidad del admin autenticado
        $todosLosTickets = Ticket::with(['user', 'categoria', 'estado'])
            ->whereHas('categoria', function ($q) use ($miUnidadId) {
                $q->where('unidad_id', $miUnidadId);
            })
            ->whereBetween('created_at', [$inicioMes, $finMes])
            ->latest()
            ->get();

        //--tickets asignados al admin autenticado
        $ticketsAsignados = Ticket::where('tecnico_id', Auth::id())
            ->where('estado_id', 2)
            ->count();

        //----Estadísticas mensuales filtradas por Unidad de Categoría----
        $añoActual = date('Y');
        $nombresMeses = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];
        $mesesGrafico = [];

        //---agrupa tickets por mes y estado
        $statsMensuales = Ticket::selectRaw('MONTH(created_at) as mes, estado_id, COUNT(*) as total')
            ->whereYear('created_at', $añoActual)
            ->whereHas('categoria', function ($q) use ($miUnidadId) {
                $q->where('unidad_id', $miUnidadId);
            })
            ->groupBy('mes', 'estado_id')
            ->get();

        for ($i = 1; $i <= 12; $i++) {
            //---tickets resueltos
            $res = $statsMensuales->where('mes', $i)->whereIn('estado_id', $estadosCerrados)->sum('total');

            //--sumamos los pendientes   
            $pen = $statsMensuales->where('mes', $i)->whereNotIn('estado_id', $estadosCerrados)->sum('total');

            $total = $res + $pen;

            $mesesGrafico[] = [
                'nombre' => $nombresMeses[$i - 1],
                'resueltos_pct' => $total > 0 ? round(($res / $total) * 100) : 0,
                'pendientes_pct' => $total > 0 ? round(($pen / $total) * 100) : 0,
                'total' => $total
            ];
        }

        //----manuales
        $categorias = CategoriaManual::orderBy('nombre_categoria_manual')->get();
        $manuales = Manual::with('categoria')->latest()->get();

        return view('admin.dashboard', compact('noAsignados', 'pendientes', 'resueltos', 'todosLosTickets', 'mesesGrafico', 'categorias', 'manuales', 'ticketsAsignados'));
    }

    //-------------------------CLIENTE----------------------------
    public function create()
    {
        $categorias = Categoria::all();
        $tipos = TipoSolicitud::all();
        $prioridades = Prioridad::all();

        return view('admin.crear-ticket', compact('categorias', 'tipos', 'prioridades'));
    }

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
            'user_id' => Auth::id() ?? 1, //----asignar el ticket al usuario autenticado
            'estado_id' => 1, //---abierto
            'prioridad_id' => $request->prioridad_id,
            'tecnico_id' => null, //---vacio inicial 
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
                Mail::bcc($destinatarios)->send(new NuevaSolicitudUnidadMail($nuevoTicket));
            }
        } catch (\Exception $e) {
            Log::error("Error avisando a la unidad: " . $e->getMessage());
        }

        //--redireccionar con mensaje de exito o error en el correo
        return redirect()->route('admin.crear-ticket')
            ->with('success', $mensajeFlash);
    }

    //---metodos para cliente---
    public function misTickets()
    {
        $misTickets = Ticket::where('user_id', Auth::id())
            ->with(['categoria', 'tipo_solicitud', 'prioridad', 'estado', 'tecnico'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.mis-tickets', compact('misTickets'));
    }

    //----metodo para mostrar recursos
    public function recursos()
    {
        $categorias = CategoriaManual::orderBy('nombre_categoria_manual', 'asc')->get();
        $manuales = Manual::with('categoria')->latest()->get();
        return view('admin.recursos', compact('categorias', 'manuales'));
    }

    //------------------------------metodos para administracion---------------------------------------------
    public function asignarTickets()
    {
        $miUnidadId = Auth::user()->unidad_id; //---obtenemos la unidad del admin autenticado

        //--obtener todos los tickets de la unidad del admin autenticado, con sus relaciones para mostrar en la vista
        $tickets = Ticket::with(['user', 'categoria', 'estado', 'tecnico'])
            ->whereHas('categoria', function ($q) use ($miUnidadId) {
                $q->where('unidad_id', $miUnidadId);
            })
            ->where('estado_id', 1) //---solo tickets sin asignar
            ->latest()
            ->get();

        //---obtener tecnicos
        $tecnicos = User::where('unidad_id', $miUnidadId)
            ->where('activo', true)
            ->get();

        return view('admin.asignar-tickets', compact('tickets', 'tecnicos'));
    }

    //---Actualizar Prioridad---
    public function actualizarPrioridad(Request $request, Ticket $ticket)
    {
        $request->validate(['prioridad_id' => 'required|exists:prioridades,id']);
        if (in_array($ticket->estado_id, [3, 4, 5])) {
            return back()->with('sweet_error', 'No se puede modificar la prioridad este ticket ha sido resuelto o cerrado.');
        }
        $ticket->update(['prioridad_id' => $request->prioridad_id]);
        return back()->with('sweet_success', 'Prioridad actualizada correctamente');
    }

    //--- Actualizar Técnico ---
    public function actualizarTecnico(Request $request, Ticket $ticket)
    {
        $request->validate([
            'tecnico_id' => [
                'nullable',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $user = User::find($value);
                        if ($user && !$user->activo) {
                            $fail('El técnico seleccionado no está activo.');
                        }
                    }
                },
            ]
        ]);

        if (in_array($ticket->estado_id, [3, 4, 5])) {
            return back()->with('sweet_error', '¡Operación rechazada! Este ticket fue resuelto o cerrado por otro usuario hace unos momentos.');
        }

        if ($request->filled('tecnico_id')) {
            if ($ticket->tecnico_id !== null && $ticket->tecnico_id != $request->tecnico_id) {
                return back()->with('sweet_error', '¡Demasiado tarde! Otro usuario ya asignó este ticket a un técnico diferente.');
            }
        } else {
            if ($ticket->tecnico_id === null) {
                return back()->with('sweet_error', 'El ticket ya se encontraba en la cola de pendientes.');
            }
        }


        //---actualizar datos
        $ticket->update([
            'tecnico_id' => $request->tecnico_id,
            'estado_id'  => $request->tecnico_id ? 2 : 1
        ]);

        $mensaje = $request->tecnico_id
            ? 'Técnico asignado correctamente.'
            : 'Ticket devuelto a la cola de pendientes.';

        return back()->with('sweet_success', $mensaje);
    }

    //---metodo para mostrar los tickets asignados al tecnico autenticado
    public function misAsignados()
    {
        $user = Auth::user();
        $tickets = Ticket::with(['user.unidad', 'estado', 'prioridad', 'tipo_solicitud', 'categoria'])
            ->where('tecnico_id', $user->id)
            ->latest()
            ->get();

        $prioridades = Prioridad::all();
        $tecnicos = User::where('unidad_id', $user->unidad_id)
            ->where('activo', true)
            ->get();

        return view('admin.mis_asignados', compact('tickets', 'tecnicos', 'prioridades'));
    }

    //---metodo para mostrar gestion de usuarios
    public function gestionUsuarios()
    {
        $usuarios = User::all();
        return view('admin.gestion-usuarios', compact('usuarios'));
    }
    //---metodo para mostrar gestion de recursos
    public function gestionRecursos()
    {
        $categorias = CategoriaManual::orderBy('nombre_categoria_manual', 'asc')->get();
        $manuales = Manual::with('categoria')->latest()->get();
        return view('admin.gestion-recursos', compact('categorias', 'manuales'));
    }
    //---metodo para mostrar historial de tickets con filtros y métricas
    public function historial()
    {
        //--obtener todos los tickets de la unidad del admin autenticado, con sus relaciones para mostrar en la vista
        $tickets = Ticket::with(['user', 'categoria', 'estado', 'tecnico'])
            ->whereYear('created_at', date('Y'))
            ->latest()
            ->get();

        //----metricas
        $cargaTrabajo = $tickets->filter(function ($ticket) {
            return Carbon::parse($ticket->created_at)->isToday();
        })->count();

        //---tickets resueltos en las ultimas 24 horas
        $resueltos24h = $tickets->whereIn('estado_id', [3, 4, 5])
            ->filter(function ($ticket) {
                return $ticket->fecha_cierre && Carbon::parse($ticket->fecha_cierre)->gte(now()->subDay());
            })
            ->count();

        //-----tasa cierre mensual
        $ticketsDelMes = $tickets->filter(function ($ticket) {
            return Carbon::parse($ticket->created_at)->isCurrentMonth();
        });
        $totalTickets = $ticketsDelMes->count();
        $cerradosTickets = $ticketsDelMes->whereIn('estado_id', [3, 4, 5])->count();
        $tasaCierre = $totalTickets > 0 ? round(($cerradosTickets / $totalTickets) * 100) : 0;
        $estados = Estado::all();
        $categorias = Categoria::all();
        return view('admin.historial', compact('tickets', 'cargaTrabajo', 'resueltos24h', 'tasaCierre', 'estados', 'categorias'));
    }

    public function reportes()
    {
        return view('admin.reportes');
    }
}
