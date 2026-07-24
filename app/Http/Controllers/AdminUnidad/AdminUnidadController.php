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
        $categoria = Categoria::select('id', 'unidad_id')->find($categoriaId);
        $unidadId = $categoria ? $categoria->unidad_id : null;
        $horasSla = 24;

        if ($unidadId) {
            $sla = DB::table('prioridad_unidad')
                ->where('unidad_id', $unidadId)
                ->where('prioridad_id', $prioridadId)
                ->value('horas_sla');

            if ($sla) {
                $horasSla = (int)$sla;
            }
        }
        return Carbon::now()->addHours($horasSla);
    }

    public function index()
    {
        $miUnidadId = Auth::user()->unidad_id;
        $estadosCerrados = [3, 4, 5];

        // --- OPTIMIZACIÓN 1: Contadores directos filtrados por la unidad del gestor
        $baseQuery = Ticket::query();
        if ($miUnidadId) {
            $baseQuery->whereHas('categoria', fn($q) => $q->where('unidad_id', $miUnidadId));
        }

        $noAsignados = (clone $baseQuery)->whereNull('tecnico_id')->whereNotIn('estado_id', $estadosCerrados)->count();
        $pendientes  = (clone $baseQuery)->whereNotNull('tecnico_id')->where('estado_id', 2)->count();
        $resueltos   = (clone $baseQuery)->whereIn('estado_id', $estadosCerrados)
            ->whereMonth('created_at', date('m'))
            ->whereYear('created_at', date('Y'))
            ->count();

        $estadoBoton = request()->query('estado', 'todos');

        // --- OPTIMIZACIÓN 2: Carga optimizada con selección de columnas indispensables
        $queryTabla = Ticket::with([
            'user:id,name,email', 
            'categoria:id,nombre_categoria', 
            'estado:id,nombre_estado', 
            'tecnico:id,name', 
            'prioridad:id,nombre_prioridad', 
            'tipo_solicitud:id,nombre_tipo_solicitud'
        ]);

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

        // Limitamos a los últimos 100 registros para proteger la memoria RAM
        $todosLosTickets = $queryTabla->latest()->take(100)->get();

        $ticketsAsignados = Ticket::where('tecnico_id', Auth::id())
            ->where('estado_id', 2)
            ->count();

        // --- OPTIMIZACIÓN 3: Caché para las estadísticas del gráfico por Unidad (10 minutos)
        $cacheKeyStats = 'gestor_dashboard_stats_' . ($miUnidadId ?? 'global') . '_' . date('Y-m');
        $mesesGrafico = Cache::remember($cacheKeyStats, 600, function () use ($miUnidadId, $estadosCerrados) {
            $añoActual = date('Y');
            $nombresMeses = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];
            
            $statsMensuales = Ticket::selectRaw('MONTH(created_at) as mes, estado_id, COUNT(*) as total')
                ->whereYear('created_at', $añoActual)
                ->when($miUnidadId, function($q) use ($miUnidadId) {
                    $q->whereHas('categoria', fn($cat) => $cat->where('unidad_id', $miUnidadId));
                })
                ->groupBy('mes', 'estado_id')
                ->get();

            $grafico = [];
            for ($i = 1; $i <= 12; $i++) {
                $res = $statsMensuales->where('mes', $i)->whereIn('estado_id', $estadosCerrados)->sum('total');
                $pen = $statsMensuales->where('mes', $i)->whereNotIn('estado_id', $estadosCerrados)->sum('total');
                $total = $res + $pen;

                $grafico[] = [
                    'nombre' => $nombresMeses[$i - 1],
                    'resueltos_pct' => $total > 0 ? round(($res / $total) * 100) : 0,
                    'pendientes_pct' => $total > 0 ? round(($pen / $total) * 100) : 0,
                    'total' => $total
                ];
            }
            return $grafico;
        });

        return view('gestor.dashboard', compact('noAsignados', 'pendientes', 'resueltos', 'todosLosTickets', 'mesesGrafico', 'ticketsAsignados'));
    }

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
        
        if (!Cache::add($cacheKey, true, 10)) {
            return redirect()->route('gestor.crear-ticket')
                ->with('success', '¡Recibido! Tu solicitud ya se está procesando.');
        }

        $request->validate([
            'asunto' => 'required|string|min:5|max:50',
            'categoria_id' => 'required|exists:categorias,id',
            'tipo_solicitud_id' => 'required|exists:tipo_solicitudes,id',
            'descripcion' => 'required|string',
            'prioridad_id' => 'required|exists:prioridades,id',
        ]);

        $fechaVencimiento = $this->calcularFechaVencimientoSla($request->categoria_id, $request->prioridad_id);

        $rutaEvidencia = null;
        if ($request->hasFile('evidencia')) {
            $rutaEvidencia = $request->file('evidencia')->store('evidencias', 'public');
        }

        $nuevoTicket = Ticket::create([
            'asunto' => $request->asunto,
            'descripcion' => $request->descripcion,
            'drive_link' => $rutaEvidencia,
            'categoria_id' => $request->categoria_id,
            'tipo_solicitud_id' => $request->tipo_solicitud_id,
            'user_id' => $userId,
            'estado_id' => 1,
            'prioridad_id' => $request->prioridad_id,
            'tecnico_id' => null,
            'fecha_vencimiento_sla' => $fechaVencimiento,
            'estado_sla' => 'pendiente',
        ]);

        // Envíos de correos en colas seguras
        try {
            $usuario = Auth::user();
            if (!empty($usuario->email)) {
                Mail::to($usuario->email)->queue(new TicketCreadoMail($nuevoTicket));
                $mensajeFlash = '¡Ticket creado con éxito y correo enviado!';
            } else {
                $mensajeFlash = 'Ticket creado, pero el usuario no tiene email configurado.';
            }
        } catch (\Exception $e) {
            Log::error("Fallo correo Ticket #" . $nuevoTicket->id . ": " . $e->getMessage());
            $mensajeFlash = 'Ticket creado, pero no se pudo enviar el correo de confirmación.';
        }

        try {
            $unidadId = Categoria::where('id', $nuevoTicket->categoria_id)->value('unidad_id');
            if ($unidadId) {
                $destinatarios = User::where('unidad_id', $unidadId)
                    ->where('activo', true)
                    ->pluck('email')
                    ->toArray();

                if (!empty($destinatarios)) {
                    Mail::bcc($destinatarios)->queue(new NuevaSolicitudUnidadMail($nuevoTicket));
                }
            }
        } catch (\Exception $e) {
            Log::error("Error avisando a la unidad: " . $e->getMessage());
        }

        broadcast(new TicketActualizado());

        return redirect()->route('gestor.crear-ticket')->with('success', $mensajeFlash);
    }

    public function misTickets()
    {
        $misTickets = Ticket::where('user_id', Auth::id())
            ->with(['categoria', 'tipo_solicitud', 'prioridad', 'estado', 'tecnico'])
            ->latest()
            ->get();

        return view('gestor.mis-tickets', compact('misTickets'));
    }

    public function recursos()
    {
        $categorias = CategoriaManual::orderBy('nombre_categoria_manual', 'asc')->get();
        $manuales = Manual::with('categoria')->latest()->get();
        return view('gestor.recursos', compact('categorias', 'manuales'));
    }

    public function asignarTickets()
    {
        $miUnidadId = Auth::user()->unidad_id;

        $tickets = Ticket::with(['user', 'categoria', 'estado', 'tecnico'])
            ->whereHas('categoria', fn($q) => $q->where('unidad_id', $miUnidadId))
            ->where('estado_id', 1)
            ->latest()
            ->get();

        $tecnicos = User::where('unidad_id', $miUnidadId)->where('activo', true)->get();

        return view('gestor.asignar-tickets', compact('tickets', 'tecnicos'));
    }

    public function actualizarPrioridad(Request $request, Ticket $ticket)
    {
        $request->validate(['prioridad_id' => 'required|exists:prioridades,id']);

        if (in_array($ticket->estado_id, [3, 4, 5])) {
            $errorMsg = 'No se puede modificar la prioridad, este ticket ha sido resuelto o cerrado.';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $errorMsg], 422)
                : back()->with('sweet_error', $errorMsg);
        }

        $nuevaFechaVencimiento = $this->calcularFechaVencimientoSla($ticket->categoria_id, $request->prioridad_id);

        $ticket->update([
            'prioridad_id' => $request->prioridad_id,
            'fecha_vencimiento_sla' => $nuevaFechaVencimiento
        ]);

        broadcast(new TicketActualizado());

        $mensajeExito = 'Prioridad y tiempo SLA actualizados correctamente';

        return $request->expectsJson()
            ? response()->json(['success' => true, 'message' => $mensajeExito])
            : back()->with('sweet_success', $mensajeExito);
    }

    public function actualizarTecnico(Request $request, Ticket $ticket)
    {
        $request->validate([
            'tecnico_id' => [
                'nullable',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    if ($value && !User::where('id', $value)->where('activo', true)->exists()) {
                        $fail('El técnico seleccionado no está activo.');
                    }
                },
            ]
        ]);

        if (in_array($ticket->estado_id, [3, 4, 5])) {
            $errorMsg = '¡Operación rechazada! Este ticket fue resuelto o cerrado por otro usuario hace unos momentos.';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $errorMsg], 422)
                : back()->with('sweet_error', $errorMsg);
        }

        if (!$request->filled('tecnico_id') && $ticket->tecnico_id === null) {
            $errorMsg = 'El ticket ya se encontraba en la cola de pendientes.';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $errorMsg], 422)
                : back()->with('sweet_error', $errorMsg);
        }

        $ticket->update([
            'tecnico_id' => $request->tecnico_id,
            'estado_id'  => $request->tecnico_id ? 2 : 1
        ]);

        $mensaje = $request->tecnico_id
            ? 'Técnico asignado correctamente.'
            : 'Ticket devuelto a la cola de pendientes.';

        broadcast(new TicketActualizado());

        return $request->expectsJson()
            ? response()->json(['success' => true, 'message' => $mensaje])
            : back()->with('sweet_success', $mensaje);
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
        $tecnicos = User::where('unidad_id', $user->unidad_id)->where('activo', true)->get();

        return view('gestor.mis_asignados', compact('tickets', 'tecnicos', 'prioridades'));
    }

    // --- OPTIMIZACIÓN 4: Métricas del Historial procesadas en la Base de Datos
    public function historial()
    {
        $miUnidadId = Auth::user()->unidad_id;

        $queryHistorial = Ticket::with(['user:id,name', 'categoria:id,nombre_categoria', 'estado:id,nombre_estado', 'tecnico:id,name'])
            ->whereYear('created_at', date('Y'));

        if ($miUnidadId) {
            $queryHistorial->whereHas('categoria', fn($q) => $q->where('unidad_id', $miUnidadId));
        }

        $tickets = $queryHistorial->latest()->take(300)->get();

        // Métricas directas desde la Base de Datos
        $cargaTrabajo = Ticket::whereDate('created_at', Carbon::today())
            ->when($miUnidadId, fn($q) => $q->whereHas('categoria', fn($c) => $c->where('unidad_id', $miUnidadId)))
            ->count();

        $resueltos24h = Ticket::whereIn('estado_id', [3, 4, 5])
            ->where('fecha_cierre', '>=', Carbon::now()->subDay())
            ->when($miUnidadId, fn($q) => $q->whereHas('categoria', fn($c) => $c->where('unidad_id', $miUnidadId)))
            ->count();

        $totalTicketsMes = Ticket::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->when($miUnidadId, fn($q) => $q->whereHas('categoria', fn($c) => $c->where('unidad_id', $miUnidadId)))
            ->count();

        $cerradosTicketsMes = Ticket::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->whereIn('estado_id', [3, 4, 5])
            ->when($miUnidadId, fn($q) => $q->whereHas('categoria', fn($c) => $c->where('unidad_id', $miUnidadId)))
            ->count();

        $tasaCierre = $totalTicketsMes > 0 ? round(($cerradosTicketsMes / $totalTicketsMes) * 100) : 0;

        $estados = Estado::all();
        $categorias = Categoria::where('unidad_id', $miUnidadId)->get();

        return view('gestor.historial', compact('tickets', 'cargaTrabajo', 'resueltos24h', 'tasaCierre', 'estados', 'categorias'));
    }
}