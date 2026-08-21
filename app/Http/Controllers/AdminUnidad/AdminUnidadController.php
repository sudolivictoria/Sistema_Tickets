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
use App\Models\Unidad;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Interfaces\ImageManagerInterface;

class AdminUnidadController extends Controller
{
    /**
     * Comprime (GD, calidad 75, máx. 1920px) y guarda la evidencia del ticket en el disco 'public'.
     * Devuelve la ruta relativa guardada.
     */
    private function guardarEvidenciaComprimida(UploadedFile $archivo, ImageManagerInterface $imageManager): string
    {
        $nombre = Str::uuid() . '.jpg';

        $imagenComprimida = $imageManager
            ->decode($archivo)
            ->scaleDown(width: 1920, height: 1920)
            ->encodeUsingFileExtension('jpg', quality: 75);

        Storage::disk('public')->put('tickets/' . $nombre, (string) $imagenComprimida);

        return 'tickets/' . $nombre;
    }

    /**
     * Helper privado para calcular la fecha limite SLA segun Categoria y Prioridad
     */
    private function calcularFechaVencimientoSla($categoriaId, $prioridadId)
    {
        $categoria = Categoria::find($categoriaId);
        $horasSla = 24; //--valor por defecto

        if ($categoria && $categoria->unidad_id) {
            //----relación prioridad_unidad modelada en eloquent
            $unidadRef = (new Unidad())->forceFill(['id' => $categoria->unidad_id]);
            $prioridadPivot = $unidadRef->prioridades()->where('prioridades.id', $prioridadId)->first();

            if ($prioridadPivot) {
                $horasSla = (int) $prioridadPivot->pivot->horas_sla;
            }
        }
        return Carbon::now()->addHours($horasSla);
    }
    //-----------------------------------------------------------------
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

        //--tickets resueltos por unidad del admin autenticado (mes de cierre, sin importar cuándo se abrieron)
        $queryResueltos = Ticket::whereIn('estado_id', $estadosCerrados)
            ->whereMonth('fecha_cierre', date('m'))
            ->whereYear('fecha_cierre', date('Y'));

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

        $queryTabla = Ticket::with(['user.unidad', 'categoria', 'estado', 'tecnico', 'prioridad', 'tipo_solicitud']);

        if ($estadoBoton === 'resuelto,equivocado,no corresponde' || $estadoBoton === 'cerrado') {
            $queryTabla->whereIn('estado_id', $estadosCerrados)
                ->whereMonth('fecha_cierre', date('m'))
                ->whereYear('fecha_cierre', date('Y'));
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


    //---------------------------------------------------------------------------------//
    //-------------------------------CLIENTE-------------------------------------------//
    //---------------------------------------------------------------------------------//

    //--metodo para crear ticket
    public function create()
    {
        $categorias = Categoria::all();
        $tipos = TipoSolicitud::all();
        $prioridades = Prioridad::all();

        return view('gestor.crear-ticket', compact('categorias', 'tipos', 'prioridades'));
    }

    //---metodo para guardar ticket
    public function store(Request $request, ImageManagerInterface $imageManager)
    {
        $userId = Auth::id();
        $checkSum = md5($userId . trim($request->asunto));
        $cacheKey = 'submit_lock_' . $checkSum;
        if (!Cache::add($cacheKey, true, 20)) {
            $mensajeDuplicado = '¡Recibido! Tu solicitud ya se está procesando.';
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => $mensajeDuplicado]);
            }
            return redirect()->route('gestor.mis-tickets')->with('success', $mensajeDuplicado);
        }

        //-----validacion datos
        $request->validate([
            'asunto' => 'required|string|min:5|max:50',
            'categoria_id' => 'required|exists:categorias,id',
            'tipo_solicitud_id' => 'required|exists:tipo_solicitudes,id',
            'descripcion' => 'required|string',
            'prioridad_id' => 'required|exists:prioridades,id',
            'evidencia' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',

        ]);

        //----SLA utilizando la función privada
        $fechaVencimiento = $this->calcularFechaVencimientoSla($request->categoria_id, $request->prioridad_id);
        //----almacener imagenes de evidencia
        $rutaEvidencia = null;
        if ($request->hasFile('evidencia')) {
            $rutaEvidencia = $this->guardarEvidenciaComprimida($request->file('evidencia'), $imageManager);
        }

        try {
            //---------insert + relaciones + correos + broadcast son atómicos.
            $resultado = DB::transaction(function () use ($request, $rutaEvidencia, $fechaVencimiento) {
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
                //---se excluye al propio usuario (es quien comentó, dueño del ticket) y se limita a solo avisos operativos
                $destinatarios = User::where('unidad_id', $unidadId)
                    ->where('activo', true)
                    ->where('id', '!=', $usuario->id)
                    ->whereHas('rol', fn($q) => $q->whereIn('nombre_rol', ['Admin', 'Gestor']))
                    ->pluck('email')
                    ->toArray();

                if (!empty($destinatarios)) {
                    //--bcc para enviar a todos los gestores sin mostrar los emails entre ellos
                    Mail::bcc($destinatarios)->queue(new NuevaSolicitudUnidadMail($nuevoTicket));
                }

                broadcast(new TicketActualizado());

                return ['ticket' => $nuevoTicket, 'mensaje' => $mensajeFlash];
            });

            //--redireccionar con mensaje de exito
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => $resultado['mensaje']]);
            }
            return redirect()->route('gestor.mis-tickets')->with('success', $resultado['mensaje']);
        } catch (\Exception $e) {
            Cache::forget($cacheKey);
            Log::error("Error al crear ticket (Gestor): " . $e->getMessage());
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Ocurrió un error al registrar el ticket.'], 500);
            }
            return back()->withInput()->with('sweet_error', 'Ocurrió un error al registrar el ticket.');
        }
    }

    //--metodo para ver mis tickets 
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

        return view('gestor.mis-tickets', compact('misTickets'));
    }


    //----metodo para mostrar recursos (SIN USO)
    public function recursos()
    {
        $categorias = CategoriaManual::orderBy('nombre_categoria_manual', 'asc')->get();
        $manuales = Manual::with('categoria')->latest()->get();
        return view('gestor.recursos', compact('categorias', 'manuales'));
    }
    //---------------------------------------------------------------------------------------//
    //-------------------------------ADMINISTRACION------------------------------------------//
    //---------------------------------------------------------------------------------------//

    //-----metodo para asignar tickets
    public function asignarTickets()
    {
        $miUnidadId = Auth::user()->unidad_id; //---obtenemos la unidad del admin autenticado

        //--obtener todos los tickets de la unidad del admin autenticado, con sus relaciones para mostrar en la vista
        $tickets = Ticket::with(['user.unidad', 'categoria', 'estado', 'tecnico'])
            ->whereHas('categoria', function ($q) use ($miUnidadId) {
                $q->where('unidad_id', $miUnidadId);
            })
            ->where('estado_id', 1) //---solo tickets sin asignar
            ->latest()
            ->get();

        //-----obtener tecnicos
        $tecnicos = User::where('unidad_id', $miUnidadId)
            ->where('activo', true)
            ->whereHas('rol', fn($q) => $q->whereIn('nombre_rol', ['Admin', 'Gestor']))
            ->get();

        return view('gestor.asignar-tickets', compact('tickets', 'tecnicos'));
    }

    //--------------------METODOS PARA ASIGNAR Y MIS ASIGNADOS-----------------------------------

    //---Actualizar Técnico------------------------------------------->
    public function actualizarTecnico(Request $request, Ticket $ticket)
    {
        $miUnidadId = Auth::user()?->unidad_id;

        $request->validate([
            'tecnico_id' => [
                'nullable',
                'exists:users,id',
                function ($attribute, $value, $fail) use ($ticket, $miUnidadId) {
                    if ($value) {
                        $user = User::find($value);
                        if ($user && !$user->activo) {
                            $fail('El técnico seleccionado no está activo.');
                            return;
                        }
                        $unidadTicket = $miUnidadId ?: $ticket->categoria?->unidad_id;
                        if ($user && $unidadTicket && $user->unidad_id !== $unidadTicket) {
                            $fail('El técnico seleccionado no pertenece a la unidad de este ticket.');
                        }
                    }
                },
            ]
        ]);

        //------------transacción + bloqueo de fila: evita que dos usuarios asignen el mismo ticket a la vez
        $resultado = DB::transaction(function () use ($request, $ticket, $miUnidadId) {
            $ticketBloqueado = Ticket::where('id', $ticket->id)->lockForUpdate()->firstOrFail();

            //-----validacion de unidad: evita que un Gestor modifique tickets de otra unidad (IDOR)
            if ($miUnidadId && $ticketBloqueado->categoria?->unidad_id !== $miUnidadId) {
                return [
                    'error' => true,
                    'message' => 'No tienes permiso para modificar este ticket.'
                ];
            }

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

        $miUnidadId = Auth::user()?->unidad_id;

        //----------transacción + bloqueo de fila: evita que un cambio de estado concurrente
        $resultado = DB::transaction(function () use ($request, $ticket, $miUnidadId) {
            $ticketBloqueado = Ticket::where('id', $ticket->id)->lockForUpdate()->firstOrFail();

            //-----validacion de unidad: evita que un Gestor modifique tickets de otra unidad (IDOR)
            if ($miUnidadId && $ticketBloqueado->categoria?->unidad_id !== $miUnidadId) {
                return [
                    'error' => true,
                    'message' => 'No tienes permiso para modificar este ticket.'
                ];
            }

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

            return ['error' => false, 'fecha_vencimiento_sla' => $nuevaFechaVencimiento];
        });

        if ($resultado['error']) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $resultado['message']], 422);
            }
            return back()->with('sweet_error', $resultado['message']);
        }

        //------el cambio de prioridad ya se guardó; un fallo de Reverb no debe mostrarse como error al usuario
        try {
            broadcast(new TicketActualizado());
        } catch (\Exception $e) {
            Log::error('Fallo al emitir broadcast TicketActualizado (actualizarPrioridad): ' . $e->getMessage());
        }

        $mensajeExito = 'Prioridad y tiempo SLA actualizados correctamente';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $mensajeExito,
                'fecha_vencimiento_sla' => $resultado['fecha_vencimiento_sla']->format('c'),
            ]);
        }

        return back()->with('sweet_success', $mensajeExito);
    }


    //------metodo para mostrar los tickets asignados al tecnico autenticado
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
            ->whereHas('rol', fn($q) => $q->whereIn('nombre_rol', ['Admin', 'Gestor']))
            ->get();
        return view('gestor.mis_asignados', compact('tickets', 'tecnicos', 'prioridades'));
    }
    //---metodo para mostrar historial de tickets con filtros y metricas
    public function historial()
    {
        $miUnidadId = Auth::user()->unidad_id; //---obtenemos la unidad del admin autenticado

        //--obtener todos los tickets de la unidad del admin autenticado, con sus relaciones para mostrar en la vista
        $tickets = Ticket::with(['user.unidad', 'categoria', 'estado', 'tecnico'])
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
