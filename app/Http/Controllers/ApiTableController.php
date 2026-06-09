<?php

namespace App\Http\Controllers;

use App\Models\Manual;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class ApiTableController extends Controller
{
    private const TIPOS_VALIDOS = ['dashboard', 'usuario', 'asignar', 'mis_tickets', 'mis_asignados', 'historial'];
    private const TIPOS_SOLO_CONTENIDO = ['recursos'];
    private const TIPOS_SOLO_STAFF = ['asignar', 'historial', 'mis_asignados'];
    private const ESTADOS_CERRADOS = [3, 4, 5];

    public function refresh(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'No autenticado.'], 401);
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        // CORRECCIÓN: Leer 'tipo' y 'estado' desde el input (sirve tanto para query string como para JSON body)
        $tipo = (string) $request->input('tipo', 'dashboard');
        $estadoFiltro = $request->input('estado', 'todos');

        if (!in_array($tipo, self::TIPOS_VALIDOS, true) && !in_array($tipo, self::TIPOS_SOLO_CONTENIDO, true)) {
            return response()->json(['error' => 'Tipo de tabla no válido.'], 400);
        }

        // Validación de seguridad para flujos de Staff (Admin General = 1, Admin Unidad = 3, etc.)
        if (in_array($tipo, self::TIPOS_SOLO_STAFF, true) && !in_array($user->rol_id, [1, 3, 4])) {
            return response()->json(['error' => 'No autorizado.'], 403);
        }

        try {
            $miUnidadId = $user->unidad_id ?? 0;
            $ticketsQuery = Ticket::with(['user', 'categoria', 'estado', 'tecnico']);

            // Aplicar filtros según la sección de la tabla analizada
            if ($tipo === 'usuario' || $tipo === 'mis_tickets') {
                $ticketsQuery->where('user_id', $user->id);
            } elseif (in_array($tipo, self::TIPOS_SOLO_STAFF, true) || $tipo === 'dashboard') {
                // Si es admin de unidad, restringimos por su unidad correspondiente (salvo en el dashboard general si es Admin Completo)
                if ($user->rol_id != 1 && $miUnidadId > 0) {
                    $ticketsQuery->whereHas('categoria', function ($q) use ($miUnidadId) {
                        $q->where('unidad_id', $miUnidadId);
                    });
                }
            }

            // Filtros específicos por pestaña
            if ($tipo === 'asignar') {
                $ticketsQuery->whereNull('tecnico_id')->whereNotIn('estado_id', self::ESTADOS_CERRADOS);
            } elseif ($tipo === 'mis_asignados') {
                // CORRECCIÓN: Filtra los asignados estrictamente al técnico en sesión que estén activos
                $ticketsQuery->where('tecnico_id', $user->id)->whereNotIn('estado_id', self::ESTADOS_CERRADOS);
            } elseif ($tipo === 'historial') {
                $ticketsQuery->whereYear('created_at', date('Y'));
            }

            // Filtro por botones de estado en el Frontend
            if (!empty($estadoFiltro) && $estadoFiltro !== 'todos') {
                $ticketsQuery->where('estado_id', $estadoFiltro);
            }

            $ticketsResult = $ticketsQuery->latest()->get();

            // Renderizar la vista correspondiente
            $html = $this->renderizarVista($tipo, $ticketsResult, $miUnidadId);

            // Calcular contadores dinámicos
            $contadores = $this->calcularContadores($user, $miUnidadId, $tipo);

            // Calcular métricas si estamos en la vista de historial
            $metricas = null;
            if ($tipo === 'historial') {
                [$carga, $res24, $tasa] = $this->calcularMetricasHistorial($ticketsResult);
                $metricas = [
                    'cargaTrabajo' => $carga,
                    'resueltos24h' => $res24,
                    'tasaCierre'   => $tasa
                ];
            }

            return response()->json([
                'success'    => true,
                'html'       => $html,
                'contadores' => $contadores,
                'metricas'   => $metricas
            ]);
        } catch (Throwable $e) {
            Log::error("Error en ApiTableController: " . $e->getMessage());
            return response()->json(['error' => 'Error interno', 'msg' => $e->getMessage()], 500);
        }
    }

    private function calcularContadores($user, int $miUnidadId, string $tipo): array
    {
        $base = Ticket::query();

        // Si es cliente, sus contadores son únicamente suyos
        if ($tipo === 'usuario' || $tipo === 'mis_tickets' || $user->rol_id == 2) {
            $base->where('user_id', $user->id);
            return [
                'abiertos'  => (clone $base)->whereNull('tecnico_id')->whereNotIn('estado_id', self::ESTADOS_CERRADOS)->count(),
                'proceso'   => (clone $base)->whereNotNull('tecnico_id')->where('estado_id', 2)->count(),
                'resueltos' => (clone $base)->whereIn('estado_id', self::ESTADOS_CERRADOS)->whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->count(),
                'asignados' => 0
            ];
        }

        // Si es administrador de unidad, se restringe a su área
        if ($user->rol_id != 1 && $miUnidadId > 0) {
            $base->whereHas('categoria', function ($q) use ($miUnidadId) {
                $q->where('unidad_id', $miUnidadId);
            });
        }

        return [
            'abiertos'  => (clone $base)->whereNull('tecnico_id')->whereNotIn('estado_id', self::ESTADOS_CERRADOS)->count(),
            'proceso'   => (clone $base)->whereNotNull('tecnico_id')->where('estado_id', 2)->count(),
            'resueltos' => (clone $base)->whereIn('estado_id', self::ESTADOS_CERRADOS)->whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->count(),
            'asignados' => (clone $base)->where('tecnico_id', $user->id)->whereNotIn('estado_id', self::ESTADOS_CERRADOS)->count()
        ];
    }

    private function calcularMetricasHistorial($tickets): array
    {
        $cargaTrabajo = $tickets->filter(fn($t) => Carbon::parse($t->created_at)->isToday())->count();
        $hace24Horas  = now()->subDay();
        $resueltos24h = $tickets->whereIn('estado_id', self::ESTADOS_CERRADOS)
            ->filter(fn($t) => $t->fecha_cierre && Carbon::parse($t->fecha_cierre)->gte($hace24Horas))
            ->count();

        $delMes = $tickets->filter(fn($t) => Carbon::parse($t->created_at)->isCurrentMonth());
        $total  = $delMes->count();
        $cerrados   = $delMes->whereIn('estado_id', self::ESTADOS_CERRADOS)->count();
        $tasaCierre = $total > 0 ? (int) round(($cerrados / $total) * 100) : 0;

        return [$cargaTrabajo, $resueltos24h, $tasaCierre];
    }

    private function renderizarVista(string $tipo, $ticketsResult, int $miUnidadId): string
    {
        // Agregamos los meses por si el layout o el gráfico los requiere y evitar el error "Undefined variable"
        $mesesPorDefecto = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

        return match ($tipo) {
            'dashboard'     => view('partials.filas_dashboard', ['todosLosTickets' => $ticketsResult, 'mesesGrafico' => $mesesPorDefecto])->render(),
            'usuario'       => view('partials.filas_usuario', ['todosLosTickets' => $ticketsResult])->render(),
            'mis_tickets'   => view('partials.filas_mis_tickets', ['misTickets' => $ticketsResult])->render(),
            'historial'     => view('partials.filas_historial', ['tickets' => $ticketsResult, 'mesesGrafico' => $mesesPorDefecto])->render(),
            'recursos'      => view('partials.filas_recursos', ['manuales' => Manual::with('categoria')->latest()->get()])->render(),
            'asignar'       => view('partials.filas_asignar', [
                'tickets'  => $ticketsResult,
                'tecnicos' => User::where('unidad_id', $miUnidadId)->get()
            ])->render(),

            // ¡ESTO ES LO QUE FALTA! Vinculamos tu data-tipo="mis_asignados" con su vista real
            'mis_asignados' => view('partials.filas_mis_asignados', ['tickets' => $ticketsResult])->render(),
        };
    }
}
