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

        // --- OPTIMIZACIÓN 1: Reutilización de Base Query para Métricas
        $baseQuery = Ticket::where('user_id', $userId);

        $abiertos = (clone $baseQuery)
            ->whereNull('tecnico_id')
            ->whereNotIn('estado_id', $estadosCerrados)
            ->count();

        $enProceso = (clone $baseQuery)
            ->whereNotNull('tecnico_id')
            ->where('estado_id', 2)
            ->count();

        $resueltos = (clone $baseQuery)
            ->whereIn('estado_id', $estadosCerrados)
            ->whereMonth('created_at', date('m'))
            ->whereYear('created_at', date('Y'))
            ->count();

        // --- OPTIMIZACIÓN 2: Eager loading especificando solo las columnas requeridas
        $todosLosTickets = (clone $baseQuery)
            ->with([
                'categoria:id,nombre_categoria', 
                'tipo_solicitud:id,nombre_tipo_solicitud', 
                'prioridad:id,nombre_prioridad', 
                'estado:id,nombre_estado', 
                'tecnico:id,name'
            ])
            ->latest()
            ->take(5)
            ->get();

        // Carga ligera de catálogos para modales de creación
        $categorias = Categoria::select('id', 'nombre_categoria', 'unidad_id')->get();
        $prioridades = Prioridad::select('id', 'nombre_prioridad')->get();
        $tipos = TipoSolicitud::select('id', 'nombre_tipo_solicitud')->get();

        return view('usuario.dashboard', compact('abiertos', 'enProceso', 'resueltos', 'todosLosTickets', 'categorias', 'prioridades', 'tipos'));
    }

    public function create()
    {
        $categorias = Categoria::select('id', 'nombre_categoria')->get();
        $tipos = TipoSolicitud::select('id', 'nombre_tipo_solicitud')->get();
        $prioridades = Prioridad::select('id', 'nombre_prioridad')->get();

        return view('usuario.crear-ticket', compact('categorias', 'tipos', 'prioridades'));
    }

    public function store(Request $request)
    {
        $userId = Auth::id();
        $checkSum = md5($userId . trim($request->asunto));
        $cacheKey = 'submit_lock_' . $checkSum;

        // --- Prevenir envíos dobles o accidentales por 10 segundos
        if (!Cache::add($cacheKey, true, 10)) {
            return redirect()->route('usuario.dashboard')
                ->with('success', '¡Recibido! Tu solicitud ya se está procesando.');
        }

        $request->validate([
            'asunto' => 'required|string|min:5|max:50',
            'categoria_id' => 'required|exists:categorias,id',
            'tipo_solicitud_id' => 'required|exists:tipo_solicitudes,id',
            'descripcion' => 'required|string',
            'prioridad_id' => 'required|exists:prioridades,id',
        ]);

        // --- OPTIMIZACIÓN 3: Consulta escalar limpia para cálculo de SLA
        $unidadId = Categoria::where('id', $request->categoria_id)->value('unidad_id');
        $horasSla = 24;

        if ($unidadId) {
            $horasSlaVal = DB::table('prioridad_unidad')
                ->where('unidad_id', $unidadId)
                ->where('prioridad_id', $request->prioridad_id)
                ->value('horas_sla');

            if ($horasSlaVal) {
                $horasSla = (int)$horasSlaVal;
            }
        }
        $fechaVencimiento = Carbon::now()->addHours($horasSla);

        $rutaEvidencia = null;
        if ($request->hasFile('evidencia')) {
            $rutaEvidencia = $request->file('evidencia')->store('evidencias', 'public');
        }

        // --- Crear ticket (con corrección de sintaxis en estado_sla)
        $nuevoTicket = Ticket::create([
            'asunto' => $request->asunto,
            'descripcion' => $request->descripcion,
            'drive_link' => $rutaEvidencia,
            'categoria_id' => $request->categoria_id,
            'tipo_solicitud_id' => $request->tipo_solicitud_id,
            'user_id' => $userId,
            'estado_id' => 1, // Abierto
            'prioridad_id' => $request->prioridad_id,
            'tecnico_id' => null,
            'fecha_vencimiento_sla' => $fechaVencimiento,
            'estado_sla' => 'pendiente',
        ]);

        // --- Envío diferido en colas (Mail Queues)
        try {
            $usuario = Auth::user();
            if (!empty($usuario->email)) {
                Mail::to($usuario->email)->queue(new TicketCreadoMail($nuevoTicket));
                $mensajeFlash = '¡Ticket creado con éxito y correo enviado!';
            } else {
                Log::warning("Usuario {$usuario->id} no tiene email configurado. Ticket #" . $nuevoTicket->id);
                $mensajeFlash = 'Ticket creado, pero no se pudo enviar el correo (email no configurado).';
            }
        } catch (\Exception $e) {
            Log::error("Fallo al enviar correo de Ticket #" . $nuevoTicket->id . ": " . $e->getMessage());
            $mensajeFlash = 'Ticket creado, pero no se pudo enviar el correo de confirmación.';
        }

        // --- Notificar a gestores activos de la unidad correspondiente
        try {
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
            Log::error("Error avisando a la unidad en Ticket #" . $nuevoTicket->id . ": " . $e->getMessage());
        }

        broadcast(new TicketActualizado());

        return redirect()->route('usuario.dashboard')->with('success', $mensajeFlash);
    }

    public function misTickets()
    {
        // --- OPTIMIZACIÓN 4: Carga optimizada de la lista completa del cliente
        $misTickets = Ticket::where('user_id', Auth::id())
            ->with([
                'categoria:id,nombre_categoria', 
                'tipo_solicitud:id,nombre_tipo_solicitud', 
                'prioridad:id,nombre_prioridad', 
                'estado:id,nombre_estado', 
                'tecnico:id,name'
            ])
            ->latest()
            ->get();

        return view('usuario.mis-tickets', compact('misTickets'));
    }

    public function recursos()
    {
        $categorias = CategoriaManual::orderBy('nombre_categoria_manual', 'asc')->get();
        $manuales = Manual::with('categoria:id,nombre_categoria_manual')->latest()->get();

        return view('usuario.recursos', compact('categorias', 'manuales'));
    }
}