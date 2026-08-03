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
use App\Models\Unidad;
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
    /**
     * Helper privado para calcular la fecha limite SLA segun Categoria y Prioridad
     */
    private function calcularFechaVencimientoSla($categoriaId, $prioridadId)
    {
        $categoria = Categoria::find($categoriaId);
        $horasSla = 24; //--valor por defecto

        if ($categoria && $categoria->unidad_id) {
            // Relación prioridad_unidad modelada en Eloquent (Unidad::prioridades()) en vez de DB::table crudo.
            $unidadRef = (new Unidad())->forceFill(['id' => $categoria->unidad_id]);
            $prioridadPivot = $unidadRef->prioridades()->where('prioridades.id', $prioridadId)->first();

            if ($prioridadPivot) {
                $horasSla = (int) $prioridadPivot->pivot->horas_sla;
            }
        }
        return Carbon::now()->addHours($horasSla);
    }
    //-----------------------------------------------------------------
    //--------------------------CLIENTE--------------------------------
    //-----------------------------------------------------------------

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

    //---metodo para guardar ticket (Cliente/Usuario final)
    public function store(Request $request)
    {
        $request->validate([
            'asunto'            => 'required|string|min:5|max:50',
            'categoria_id'      => 'required|exists:categorias,id',
            'tipo_solicitud_id' => 'required|exists:tipo_solicitudes,id',
            'descripcion'       => 'required|string',
            'prioridad_id'      => 'required|exists:prioridades,id',
        ]);

        $userId   = Auth::id() ?? 1;
        $checkSum = md5($userId . $request->categoria_id . trim($request->asunto) . trim($request->descripcion));
        $cacheKey = 'submit_lock_' . $checkSum;

        if (!Cache::add($cacheKey, true, 5)) {
            return redirect()->route('usuario.dashboard')
                ->with('success', '¡Recibido! Tu solicitud ya se está procesando.');
        }

        try {
            $rutaEvidencia = null;
            if ($request->hasFile('evidencia')) {
                $rutaEvidencia = $request->file('evidencia')->store('evidencias', 'public');
            }

            $fechaVencimiento = $this->calcularFechaVencimientoSla($request->categoria_id, $request->prioridad_id);

            $nuevoTicket = DB::transaction(function () use ($request, $userId, $rutaEvidencia, $fechaVencimiento) {
                return Ticket::create([
                    'asunto'                => $request->asunto,
                    'descripcion'           => $request->descripcion,
                    'drive_link'            => $rutaEvidencia,
                    'categoria_id'          => $request->categoria_id,
                    'tipo_solicitud_id'     => $request->tipo_solicitud_id,
                    'user_id'               => $userId,
                    'estado_id'             => 1,
                    'prioridad_id'          => $request->prioridad_id,
                    'tecnico_id'            => null,
                    'fecha_vencimiento_sla' => $fechaVencimiento,
                    'estado_sla'            => 'pendiente',
                ]);
            });

            $nuevoTicket->load(['user', 'categoria.unidad', 'prioridad', 'tipo_solicitud']);

            $mensajeFlash = '¡Ticket creado con éxito!';
            try {
                $usuario = Auth::user();
                $destinatario = $usuario?->email;

                if (empty($destinatario)) {
                    Log::warning("Usuario {$userId} no tiene email configurado. Ticket #" . $nuevoTicket->id);
                    $mensajeFlash = 'Ticket creado, pero no se pudo enviar el correo (email no configurado).';
                } else {
                    Mail::to($destinatario)->queue(new TicketCreadoMail($nuevoTicket));
                    $mensajeFlash = '¡Ticket creado con éxito y correo enviado!';
                }
            } catch (\Exception $e) {
                Log::error("Fallo al enviar correo de Ticket #" . $nuevoTicket->id . ": " . $e->getMessage());
                $mensajeFlash = 'Ticket creado, pero no se pudo enviar el correo de confirmación.';
            }

            try {
                $unidadId = $nuevoTicket->categoria->unidad_id ?? null;
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

            // El ticket ya se guardó; un fallo de Reverb no debe reportarse como error al usuario
            try {
                broadcast(new TicketActualizado());
            } catch (\Exception $e) {
                Log::error("Fallo al emitir broadcast TicketActualizado (Ticket #{$nuevoTicket->id}): " . $e->getMessage());
            }

            return redirect()->route('usuario.dashboard')->with('success', $mensajeFlash);
        } catch (\Exception $e) {
            Cache::forget($cacheKey);
            Log::error("Error al crear ticket (Cliente): " . $e->getMessage());
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

        return view('usuario.mis-tickets', compact('misTickets'));
    }


    //--metodo para mostrar recursos (SIN USO)
    public function recursos()
    {
        $categorias = CategoriaManual::orderBy('nombre_categoria_manual', 'asc')->get();
        $manuales = Manual::with('categoria')->latest()->get();
        return view('usuario.recursos', compact('categorias', 'manuales'));
    }
}
