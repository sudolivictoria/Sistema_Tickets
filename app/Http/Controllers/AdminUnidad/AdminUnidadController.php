<?php

namespace App\Http\Controllers\AdminUnidad;

use App\Events\TicketActualizado;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AdminUnidadController extends Controller
{
    /**
     * Helper privado para calcular la fecha limite SLA segun Categoria y Prioridad
     */
    private function calcularFechaVencimientoSla($categoriaId, $prioridadId)
    {
        $categoria = Categoria::find($categoriaId);
        $unidadId = $categoria ? $categoria->unidad_id : null;
        $horasSla = 24; //--valor por defecto

        if ($unidadId) {
            $sla = DB::table('prioridad_unidad')
                ->where('unidad_id', $unidadId)
                ->where('prioridad_id', $prioridadId)
                ->first();

            if ($sla && isset($sla->horas_sla)) {
                $horasSla = (int)$sla->horas_sla;
            }
        }
        return Carbon::now()->addHours($horasSla);
    }

    public function index()
    {
        //--unidad del admin autenticado
        $miUnidadId = Auth::user()->unidad_id;
        //---estados cerrados
        $estadosCerrados = [3, 4, 5];

        //--tickets asignados por unidad del admin autenticado
        $queryAbiertos = Ticket::whereNull('tecnico_id')
            ->whereNotIn('estado_id', $estadosCerrados);

        //--tickets pendientes por unidad del admin autenticado
        $queryProceso = Ticket::whereNotNull('tecnico_id')
            ->where('estado_id', 2);

        //--tickets resueltos por unidad del admin autenticado
        $queryResueltos = Ticket::whereIn('estado_id', $estadosCerrados)
            ->whereMonth('created_at', date('m'))
            ->whereYear('created_at', date('Y'));

        //------FILTRO POR UNIDAD DE CATEGORÍA------
        if ($miUnidadId) {
            $filterUnidad = fn($q) => $q->where('unidad_id', $miUnidadId);
            $queryAbiertos->whereHas('categoria', $filterUnidad);
            $queryProceso->whereHas('categoria', $filterUnidad);
            $queryResueltos->whereHas('categoria', $filterUnidad);
        }

        //--------EJECUTAR CONTADORES----------
        $noAsignados = $queryAbiertos->count();
        $pendientes  = $queryProceso->count();
        $resueltos   = $queryResueltos->count();

        $estadoBoton = request()->query('estado', 'todos');

        $queryTabla = Ticket::with(['user', 'categoria', 'estado', 'tecnico', 'prioridad', 'tipo_solicitud']);

        if ($estadoBoton === 'resuelto,equivocado,no corresponde' || $estadoBoton === 'cerrado') {
            $queryTabla->whereIn('estado_id', $estadosCerrados)
                ->whereMonth('created_at', date('m'))
                ->whereYear('created_at', date('Y'));
        } else {
            $queryTabla->whereNotIn('estado_id', $estadosCerrados);
        }

        if ($miUnidadId) {
            $queryTabla->whereHas('categoria', fn($q) => $q->where('unidad_id', $miUnidadId));
        }

        $todosLosTickets = $queryTabla->latest()->get();

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
        //$categorias = CategoriaManual::orderBy('nombre_categoria_manual')->get();
        //$manuales = Manual::with('categoria')->latest()->get();

        return view('gestor.dashboard', compact('noAsignados', 'pendientes', 'resueltos', 'todosLosTickets', 'mesesGrafico', 'ticketsAsignados'));
    }


    //-------------------------CLIENTE----------------------------
    public function create()
    {
        $categorias = Categoria::all();
        $tipos = TipoSolicitud::all();
        $prioridades = Prioridad::all();

        return view('gestor.crear-ticket', compact('categorias', 'tipos', 'prioridades'));
    }

    public function store(Request $request)
    {
        $userId = Auth::id();
        $checkSum = md5($userId . trim($request->asunto));
        $cacheKey = 'submit_lock_' . $checkSum;
        if (!Cache::add($cacheKey, true, 20)) {
            return redirect()->route('gestor.crear-ticket')
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

        //----SLA utilizando la función privada
        $fechaVencimiento = $this->calcularFechaVencimientoSla($request->categoria_id, $request->prioridad_id);

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
            'user_id' => Auth::id(), //----asignar el ticket al usuario autenticado
            'estado_id' => 1, //---abierto
            'prioridad_id' => $request->prioridad_id,
            'tecnico_id' => null, //---vacio inicial 
            'fecha_vencimiento_sla' => $fechaVencimiento,
            'estado_sla' => 'pendiente',
        ]);

        //---cargar relaciones para el correo
        $nuevoTicket->load(['user', 'categoria', 'prioridad', 'tipo_solicitud']);

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
                ->where('activo', true)
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
        return redirect()->route('gestor.crear-ticket')
            ->with('success', $mensajeFlash);
    }

    //--metodos lado del cliente
    public function misTickets()
    {
        $misTickets = Ticket::where('user_id', Auth::id())
            ->with(['categoria', 'tipo_solicitud', 'prioridad', 'estado', 'tecnico'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('gestor.mis-tickets', compact('misTickets'));
    }

    public function recursos()
    {
        $categorias = CategoriaManual::orderBy('nombre_categoria_manual', 'asc')->get();
        $manuales = Manual::with('categoria')->latest()->get();
        return view('gestor.recursos', compact('categorias', 'manuales'));
    }

    //------------------------------metodos del lado del administrador---------------------------------------------
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

        $tecnicos = User::where('unidad_id', $miUnidadId)
            ->where('activo', true)
            ->get();

        return view('gestor.asignar-tickets', compact('tickets', 'tecnicos'));
    }

    //---Actualizar Prioridad----------------------------------------------------->
    public function actualizarPrioridad(Request $request, Ticket $ticket)
    {
        $request->validate(['prioridad_id' => 'required|exists:prioridades,id']);

        if (in_array($ticket->estado_id, [3, 4, 5])) {
            $errorMsg = 'No se puede modificar la prioridad, este ticket ha sido resuelto o cerrado.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 422);
            }
            return back()->with('sweet_error', $errorMsg);
        }

        //---recalcular SLA
        $nuevaFechaVencimiento = $this->calcularFechaVencimientoSla($ticket->categoria_id, $request->prioridad_id);

        $ticket->update([
            'prioridad_id' => $request->prioridad_id,
            'fecha_vencimiento_sla' => $nuevaFechaVencimiento
        ]);

        broadcast(new TicketActualizado());

        $mensajeExito = 'Prioridad y tiempo SLA actualizados correctamente';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $mensajeExito]);
        }

        return back()->with('sweet_success', $mensajeExito);
    }

    //---Actualizar Técnico--------------------------------------------------->
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
            $errorMsg = '¡Operación rechazada! Este ticket fue resuelto o cerrado por otro usuario hace unos momentos.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 422);
            }
            return back()->with('sweet_error', $errorMsg);
        }

        if (!$request->filled('tecnico_id') && $ticket->tecnico_id === null) {
            $errorMsg = 'El ticket ya se encontraba en la cola de pendientes.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 422);
            }
            return back()->with('sweet_error', $errorMsg);
        }

        $ticket->update([
            'tecnico_id' => $request->tecnico_id,
            'estado_id'  => $request->tecnico_id ? 2 : 1
        ]);

        $mensaje = $request->tecnico_id
            ? 'Técnico asignado correctamente.'
            : 'Ticket devuelto a la cola de pendientes.';

        broadcast(new TicketActualizado());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $mensaje]);
        }

        return back()->with('sweet_success', $mensaje);
    }

    public function misAsignados()
    {
        $user = Auth::user();
        $tickets = Ticket::with(['user.unidad', 'estado', 'prioridad', 'tipo_solicitud', 'categoria'])
            ->where('tecnico_id', $user->id)
            ->where('estado_id', 2)
            ->latest()
            ->get();

        $prioridades = Prioridad::all();
        $tecnicos = User::where('unidad_id', $user->unidad_id)
            ->where('activo', true)
            ->get();
        return view('gestor.mis_asignados', compact('tickets', 'tecnicos', 'prioridades'));
    }

    public function historial()
    {
        $miUnidadId = Auth::user()->unidad_id; //---obtenemos la unidad del admin autenticado

        //--obtener todos los tickets de la unidad del admin autenticado, con sus relaciones para mostrar en la vista
        $tickets = Ticket::with(['user', 'categoria', 'estado', 'tecnico'])
            ->whereYear('created_at', date('Y'))
            ->whereHas('categoria', function ($q) use ($miUnidadId) {
                $q->where('unidad_id', $miUnidadId);
            })
            ->latest()
            ->get();

        //----metricas
        $cargaTrabajo = $tickets->filter(function ($ticket) {
            return Carbon::parse($ticket->created_at)->isToday();
        })->count();

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
        $categorias = Categoria::where('unidad_id', $miUnidadId)->get();

        return view('gestor.historial', compact('tickets', 'cargaTrabajo', 'resueltos24h', 'tasaCierre', 'estados', 'categorias'));
    }
}
