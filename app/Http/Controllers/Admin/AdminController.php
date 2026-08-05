<?php

namespace App\Http\Controllers\Admin;

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
use App\Models\Unidad;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    /**
     * Helper privado para calcular la fecha limite SLA segun Categoria y Prioridad
     */
    private function calcularFechaVencimientoSla($categoriaId, $prioridadId)
    {
        $categoria = Categoria::find($categoriaId);
        $horasSla = 24; //--valor por defecto

        if ($categoria && $categoria->unidad_id) {
            //---relación prioridad_unidad modelada en Eloquent (Unidad::prioridades()) en vez de DB::table crudo.
            //---no hace falta cargar la Unidad completa: basta con su id para consultar el pivot.
            $unidadRef = (new Unidad())->forceFill(['id' => $categoria->unidad_id]);
            $prioridadPivot = $unidadRef->prioridades()->where('prioridades.id', $prioridadId)->first();

            if ($prioridadPivot) {
                $horasSla = (int) $prioridadPivot->pivot->horas_sla;
            }
        }
        return Carbon::now()->addHours($horasSla);
    }
    //------------------------------------------------------------------------------

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

        //--tickets resueltos por unidad del admin autenticado (mes)
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

        //--captura el filtrado
        $estadoBoton = request()->query('estado', 'todos');

        //-----tickets
        $queryTabla = Ticket::with(['user', 'categoria', 'estado', 'tecnico', 'prioridad', 'tipo_solicitud']);

        //----filtrado por estado
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
            //-------grafico------------
            $mesesGrafico[] = [
                'nombre' => $nombresMeses[$i - 1],
                'resueltos_pct' => $total > 0 ? round(($res / $total) * 100) : 0,
                'pendientes_pct' => $total > 0 ? round(($pen / $total) * 100) : 0,
                'total' => $total
            ];
        }

        $estadosCerrados = [3, 4, 5];
        $queryPrioridades = Ticket::whereNotIn('estado_id', $estadosCerrados);
        if ($miUnidadId) {
            $queryPrioridades->whereHas('categoria', fn($q) => $q->where('unidad_id', $miUnidadId));
        }
        //------------------prioridades----------------------
        $prioridades = [
            'critica' => (clone $queryPrioridades)->where('prioridad_id', 1)->count(),
            'alta'    => (clone $queryPrioridades)->where('prioridad_id', 2)->count(),
            'media'   => (clone $queryPrioridades)->where('prioridad_id', 3)->count(),
            'baja'    => (clone $queryPrioridades)->where('prioridad_id', 4)->count(),
        ];

        //----manuales
        //$categorias = CategoriaManual::orderBy('nombre_categoria_manual')->get();
        //$manuales = Manual::with('categoria')->latest()->get();
        return view('admin.dashboard', compact('noAsignados', 'pendientes', 'resueltos', 'todosLosTickets', 'mesesGrafico', 'ticketsAsignados', 'prioridades'));
    }

    //---------------------------------------------------------------------------------//
    //-------------------------------CLIENTE-------------------------------------------//
    //---------------------------------------------------------------------------------//

    //--metodo para crear ticket
    public function create()
    {
        $categorias = Categoria::all();
        $tipos = TipoSolicitud::all();
        $prioridades = Prioridad::all();

        return view('admin.crear-ticket', compact('categorias', 'tipos', 'prioridades'));
    }

    //---metodo para guardar ticket
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

        //----SLA utilizando la función privada
        $fechaVencimiento = $this->calcularFechaVencimientoSla($request->categoria_id, $request->prioridad_id);
        //----almacenar imagenes de evidencia
        $rutaEvidencia = null;
        if ($request->hasFile('evidencia')) {
            $rutaEvidencia = $request->file('evidencia')->store('evidencias', 'public');
        }

        try {
            //----insert + relaciones + correos + broadcast son atómicos.
            //----si el correo o el broadcast lanzan una excepción, se revierte el ticket
            $resultado = DB::transaction(function () use ($request, $rutaEvidencia, $fechaVencimiento) {
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
                    'estado_sla' => 'pendiente',

                ]);

                //---cargar relaciones para el correo
                $nuevoTicket->load(['user', 'categoria.unidad', 'prioridad', 'tipo_solicitud']);

                //************************************************************************************/
                //-----------------------CORREO CONFIRMACION CLIENTE----------------------------------
                //***********************************************************************************/
                //---obtenemos el email del usuario autenticado
                $usuario = Auth::user();
                $destinatario = $usuario->email;

                if (empty($destinatario)) {
                    Log::warning("Usuario {$usuario->id} no tiene email configurado. Ticket #" . $nuevoTicket->id);
                    $mensajeFlash = 'Ticket creado, pero no se pudo enviar el correo (email no configurado).';
                } else {
                    Mail::to($destinatario)->queue(new TicketCreadoMail($nuevoTicket));
                    $mensajeFlash = '¡Ticket creado con éxito y correo enviado!';
                }

                //********************************************************************************/
                //----------------------------NOTIFICACION UNIDAD-----------------------------------
                //********************************************************************************/
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

                //---websocket
                broadcast(new TicketActualizado());

                return ['ticket' => $nuevoTicket, 'mensaje' => $mensajeFlash];
            });

            //--redireccionar con mensaje de exito
            return redirect()->route('admin.crear-ticket')
                ->with('success', $resultado['mensaje']);
        } catch (\Exception $e) {
            Cache::forget($cacheKey);
            Log::error("Error al crear ticket (Admin): " . $e->getMessage());
            return back()->withInput()->with('sweet_error', 'Ocurrió un error al registrar el ticket.');
        }
    }

    //----metodo para ver mis tickets
    public function misTickets()
    {
        $estadosCerrados = [3, 4, 5];
        $añoActual = date('Y');

        $misTickets = Ticket::where('user_id', Auth::id())
            ->where(function ($query) use ($añoActual, $estadosCerrados) {
                $query->whereYear('created_at', $añoActual)
                    ->orWhereNotIn('estado_id', $estadosCerrados);
            })
            ->with(['categoria', 'tipo_solicitud', 'prioridad', 'estado', 'tecnico'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.mis-tickets', compact('misTickets'));
    }


    //----metodo para mostrar recursos (SIN USO)
    public function recursos()
    {
        $categorias = CategoriaManual::orderBy('nombre_categoria_manual', 'asc')->get();
        $manuales = Manual::with('categoria')->latest()->get();
        return view('admin.recursos', compact('categorias', 'manuales'));
    }

    //---------------------------------------------------------------------------------------//
    //-------------------------------ADMINISTRACION------------------------------------------//
    //---------------------------------------------------------------------------------------//

    //---------metodo para asignar tickets
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

    //---------------------------METODOS PARA ASIGNAR Y MIS ASIGNADOS------------------------>

    //---Actualizar Técnico------------------------------------------->
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

        //-----Transacción + bloqueo de fila: evita que dos usuarios asignen el mismo ticket a la vez
        $resultado = DB::transaction(function () use ($request, $ticket) {
            $ticketBloqueado = Ticket::where('id', $ticket->id)->lockForUpdate()->firstOrFail();

            //-----validacion que no este cerrado
            if (in_array($ticketBloqueado->estado_id, [3, 4, 5])) {
                return [
                    'error' => true,
                    'message' => '¡Operación rechazada! Este ticket fue resuelto o cerrado por otro usuario hace unos momentos.'
                ];
            }
            //------validacion cola de pendientes
            if (!$request->filled('tecnico_id') && $ticketBloqueado->tecnico_id === null) {
                return [
                    'error' => true,
                    'message' => 'El ticket ya se encontraba en la cola de pendientes.'
                ];
            }
            //----------cambio de estado o tecnico
            $ticketBloqueado->update([
                'tecnico_id' => $request->tecnico_id,
                'estado_id'  => $request->tecnico_id ? 2 : 1
            ]);

            return ['error' => false];
        });

        if ($resultado['error']) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $resultado['message']], 422);
            }
            return back()->with('sweet_error', $resultado['message']);
        }

        $mensaje = $request->tecnico_id
            ? 'Técnico asignado correctamente.'
            : 'Ticket devuelto a la cola de pendientes.';

        //-------La asignación ya se guardó; un fallo de Reverb no debe mostrarse como error al usuario
        try {
            broadcast(new TicketActualizado()); //-------------tiempo real
        } catch (\Exception $e) {
            Log::error('Fallo al emitir broadcast TicketActualizado (actualizarTecnico): ' . $e->getMessage());
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $mensaje]);
        }
        return back()->with('sweet_success', $mensaje);
    }

    //---Actualizar Prioridad----------------------------------------------------->
    public function actualizarPrioridad(Request $request, Ticket $ticket)
    {
        $request->validate(['prioridad_id' => 'required|exists:prioridades,id']);

        //--------Transacción + bloqueo de fila: evita que un cambio de estado concurrente 
        $resultado = DB::transaction(function () use ($request, $ticket) {
            $ticketBloqueado = Ticket::where('id', $ticket->id)->lockForUpdate()->firstOrFail();

            if (in_array($ticketBloqueado->estado_id, [3, 4, 5])) {
                return [
                    'error' => true,
                    'message' => 'No se puede modificar la prioridad, este ticket ha sido resuelto o cerrado.'
                ];
            }

            //---recalcular SLA
            $nuevaFechaVencimiento = $this->calcularFechaVencimientoSla($ticketBloqueado->categoria_id, $request->prioridad_id);

            $ticketBloqueado->update([
                'prioridad_id' => $request->prioridad_id,
                'fecha_vencimiento_sla' => $nuevaFechaVencimiento
            ]);

            return ['error' => false];
        });

        if ($resultado['error']) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $resultado['message']], 422);
            }
            return back()->with('sweet_error', $resultado['message']);
        }

        //------El cambio de prioridad ya se guardó; un fallo de Reverb no debe mostrarse como error al usuario
        try {
            broadcast(new TicketActualizado());
        } catch (\Exception $e) {
            Log::error('Fallo al emitir broadcast TicketActualizado (actualizarPrioridad): ' . $e->getMessage());
        }

        $mensajeExito = 'Prioridad y tiempo SLA actualizados correctamente';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $mensajeExito]);
        }

        return back()->with('sweet_success', $mensajeExito);
    }

    //---metodo para mostrar los tickets asignados al tecnico autenticado
    public function misAsignados()
    {
        $user = Auth::user();
        $tickets = Ticket::with(['user.unidad', 'estado', 'prioridad', 'tipo_solicitud', 'categoria'])
            ->where('tecnico_id', $user->id)
            ->where('estado_id', 2) //---solo pendientes asignados
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
    /*public function gestionRecursos()
    {
        $categorias = CategoriaManual::orderBy('nombre_categoria_manual', 'asc')->get();
        $manuales = Manual::with('categoria')->latest()->get();
        return view('admin.gestion-recursos', compact('categorias', 'manuales'));
    }*/

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
}
