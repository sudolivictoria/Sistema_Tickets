<?php

namespace App\Http\Controllers;

use App\Models\Manual;
use App\Models\Ticket;
use App\Models\User;
use App\Models\CategoriaManual;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ApiTableController extends Controller
{
    private const TIPOS_VALIDOS = ['dashboard', 'usuario', 'asignar', 'mis_tickets', 'mis_asignados', 'historial'];
    private const TIPOS_SOLO_CONTENIDO = ['recursos'];
    private const TIPOS_SOLO_STAFF = ['asignar', 'historial', 'mis_asignados'];
    private const ESTADOS_CERRADOS = [3, 4, 5];

    /**
     * Endpoint HTTP de Consulta y Sincronización Asíncrona
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'No autenticado.'], 401);
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $tipo = (string) $request->query('tipo', 'dashboard');
        if (!in_array($tipo, self::TIPOS_VALIDOS, true) && !in_array($tipo, self::TIPOS_SOLO_CONTENIDO, true)) {
            return response()->json(['error' => 'Tipo de tabla no válido.'], 400);
        }

        if (in_array($tipo, self::TIPOS_SOLO_STAFF, true) && !in_array($user->rol_id, [1, 3])) {
            return response()->json(['error' => 'No autorizado.'], 403);
        }

        try {
            if (in_array($tipo, self::TIPOS_SOLO_CONTENIDO, true)) {
                return response()->json([
                    'html' => $this->renderizarVista($tipo, null, (int)$user->unidad_id)
                ]);
            }

            $estadoFiltro = (string) $request->query('estado', 'todos');
            $miUnidadId = (int) $user->unidad_id;

            //----------------------CONTADORES DE ESTADOS----------------------
            $queryAbiertos  = Ticket::whereNull('tecnico_id')->whereNotIn('estado_id', self::ESTADOS_CERRADOS);
            $queryProceso   = Ticket::whereNotNull('tecnico_id')->where('estado_id', 2);
            $queryResueltos = Ticket::whereIn('estado_id', self::ESTADOS_CERRADOS)
                ->whereMonth('created_at', date('m'))
                ->whereYear('created_at', date('Y'));

            //------------------------FILTRADO POR UNIDAD------------------------
            if ($miUnidadId && ($user->rol_id == 1 || $user->rol_id == 3)) {
                $filterUnidad = fn($q) => $q->where('unidad_id', $miUnidadId);
                $queryAbiertos->whereHas('categoria', $filterUnidad);
                $queryProceso->whereHas('categoria', $filterUnidad);
                $queryResueltos->whereHas('categoria', $filterUnidad);
            }

            //-------------------------FILTRADO POR USUARIO (Clientes solo ven sus tickets)-------------------------
            if ($user->rol_id == 2) {
                $queryAbiertos->where('user_id', $user->id);
                $queryProceso->where('user_id', $user->id);
                $queryResueltos->where('user_id', $user->id);
            }

            //-------------------------CONTADORES FINALES PARA LA BURBUJA DE NOTIFICACIÓN-------------------------
            $contadores = [
                'abiertos'  => $queryAbiertos->count(),
                'proceso'   => $queryProceso->count(),
                'resueltos' => $queryResueltos->count(),
            ];

            //----------------CONTADOR DE TICKETS ASIGNADOS AL USUARIO ACTUAL (Solo para técnicos)-----------------
            $contadorMisAsignados = Ticket::where('tecnico_id', $user->id)
                ->where('estado_id', 2)
                ->count();

            //--------------------------CONSULTA PRINCIPAL DE TICKETS CON FILTROS DINÁMICOS-------------------------
            $query = Ticket::with(['user', 'categoria', 'estado', 'tecnico', 'prioridad', 'tipo_solicitud']);

            $this->aplicarFiltrosBase($query, $user, $tipo, $miUnidadId);
            $this->inyectarFiltroEstado($query, $estadoFiltro);

            //--------------LIMITACIÓN DE RESULTADOS PARA LA VISTA DE USUARIOS (Solo los 5 más recientes)-----------------
            $limit = ($tipo === 'usuario') ? 5 : null;
            $ticketsResult = $limit ? $query->latest()->limit($limit)->get() : $query->latest()->get();

            //---------------------------------GENERACIÓN DEL GRÁFICO DE RENDIMIENTO ANUAL PARA EL DASHBOARD---------------------------------
            $graficoHtml = ($tipo === 'dashboard' && in_array($user->rol_id, [1, 3])) ? $this->generarGrafico($miUnidadId) : '';

            //-----------------------------------MÉTRICAS AVANZADAS PARA LA VISTA HISTORIAL (Solo para roles de staff)--------------------
            [$cargaTrabajo, $resueltos24h, $tasaCierre] = ($tipo === 'historial')
                ? $this->calcularMetricas($ticketsResult)
                : [0, 0, 0];

            //------------------------------------RENDERIZADO UNIFICADO DE FRAGMENTOS HTML PARA LA RESPUESTA AJAX------------------------------------
            return response()->json([
                'html'              => $this->renderizarVista($tipo, $ticketsResult, $miUnidadId),
                'contadores'        => $contadores,
                'grafico'           => $graficoHtml,
                'contadorAsignados' => (int) $contadorMisAsignados,
                'cargaTrabajo'      => (int) $cargaTrabajo,
                'resueltos24h'      => (int) $resueltos24h,
                'tasaCierre'        => (int) $tasaCierre,
            ]);
        } catch (Throwable $e) {
            Log::error("Error crítico en refresh: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Error interno del servidor.'], 500);
        }
    }

    /**
     * Lógica centralizada de alcance por roles, unidades y pantallas
     */
    private function aplicarFiltrosBase($query, $user, string $tipo, int $miUnidadId): void
    {
        //-----------------Clientes solo ven sus tickets en cualquier vista-----------------
        if ($tipo === 'mis_tickets' || $user->rol_id == 2) {
            $query->where('user_id', $user->id);
            return;
        }

        //------------------Dashboard principal con filtro dinámico por estado y departamento-----------------
        if ($tipo === 'dashboard') {
            $estadoBoton = request()->query('estado', 'todos');

            if ($estadoBoton === 'resuelto,equivocado,no corresponde') {
                $query->whereIn('estado_id', self::ESTADOS_CERRADOS)
                    ->whereMonth('created_at', date('m'))
                    ->whereYear('created_at', date('Y'));
            } else {
                $query->whereNotIn('estado_id', self::ESTADOS_CERRADOS);
            }
            if ($miUnidadId) {
                $query->whereHas('categoria', fn($q) => $q->where('unidad_id', $miUnidadId));
            }
            return;
        }

        if ($tipo === 'asignar' || $tipo === 'mis_asignados') {
            if ($tipo === 'mis_asignados') {
                $query->where('tecnico_id', $user->id);
            }
            if ($miUnidadId) {
                $query->whereHas('categoria', fn($q) => $q->where('unidad_id', $miUnidadId));
            }
            return;
        }

        if ($tipo === 'historial') {
            if ($miUnidadId) {
                $query->whereHas('categoria', fn($q) => $q->where('unidad_id', $miUnidadId));
            }
            return;
        }
    }

    /**
     * Mapeo de sub-filtros de estados del panel
     */
    private function inyectarFiltroEstado($query, string $estado): void
    {
        switch ($estado) {
            case 'abierto':
                $query->whereNull('tecnico_id')->whereNotIn('estado_id', self::ESTADOS_CERRADOS);
                break;

            case 'procesando':
                $query->whereNotNull('tecnico_id')->where('estado_id', 2);
                break;

            case 'resuelto,equivocado,no corresponde':
                $query->whereIn('estado_id', self::ESTADOS_CERRADOS);
                break;
        }
    }

    /**
     * Renderizado del gráfico de productividad anual para el departamento
     */
    private function generarGrafico(int $miUnidadId): string
    {
        try {
            $añoActual = (int) date('Y');
            $query = Ticket::whereYear('created_at', $añoActual);

            if ($miUnidadId) {
                $query->whereHas('categoria', fn($q) => $q->where('unidad_id', $miUnidadId));
            }

            $ticketsAño = $query->selectRaw('MONTH(created_at) as mes, estado_id')->get();

            $resueltosPorMes = array_fill(1, 12, 0);
            $totalesPorMes   = array_fill(1, 12, 0);

            foreach ($ticketsAño as $t) {
                $m = (int)$t->mes;
                if ($m >= 1 && $m <= 12) {
                    $totalesPorMes[$m]++;
                    if (in_array((int)$t->estado_id, self::ESTADOS_CERRADOS, true)) {
                        $resueltosPorMes[$m]++;
                    }
                }
            }

            $porcentajes = [];
            for ($i = 1; $i <= 12; $i++) {
                $porcentajes[] = $totalesPorMes[$i] > 0
                    ? (int) round(($resueltosPorMes[$i] / $totalesPorMes[$i]) * 100)
                    : 0;
            }

            return view('partials.grafico_rendimiento', ['porcentajesExito' => $porcentajes])->render();
        } catch (Throwable $e) {
            Log::error("Error en gráfico: " . $e->getMessage());
            return '<div class="text-danger p-4">No se pudo cargar el gráfico.</div>';
        }
    }

    /**
     * Métricas avanzadas para la vista Historial
     */
    private function calcularMetricas($tickets): array
    {
        $cargaTrabajo = $tickets->filter(fn($t) => Carbon::parse($t->created_at)->isToday())->count();

        $hace24Horas = now()->subDay();
        $resueltos24h = $tickets->whereIn('estado_id', self::ESTADOS_CERRADOS)
            ->filter(fn($t) => $t->fecha_cierre && Carbon::parse($t->fecha_cierre)->gte($hace24Horas))
            ->count();

        $delMes = $tickets->filter(fn($t) => Carbon::parse($t->created_at)->isCurrentMonth());
        $total  = $delMes->count();
        $cerrados   = $delMes->whereIn('estado_id', self::ESTADOS_CERRADOS)->count();
        $tasaCierre = $total > 0 ? (int) round(($cerrados / $total) * 100) : 0;

        return [$cargaTrabajo, $resueltos24h, $tasaCierre];
    }

    /**
     * Renderizado unificado de fragmentos HTML Blade
     */
    private function renderizarVista(string $tipo, $ticketsResult, int $miUnidadId): string
    {
        return match ($tipo) {
            'dashboard'   => view('partials.filas_dashboard', ['todosLosTickets' => $ticketsResult])->render(),
            'usuario'     => view('partials.filas_usuario', ['todosLosTickets' => $ticketsResult])->render(),
            'mis_tickets' => view('partials.filas_mis_tickets', ['misTickets' => $ticketsResult])->render(),
            'historial'   => view('partials.filas_historial', ['tickets' => $ticketsResult])->render(),
            'recursos'    => view('partials.filas_recursos', ['manuales' => Manual::with('categoria')->latest()->get()])->render(),
            'asignar' => view('partials.filas_asignar', [
                'tickets'  => $ticketsResult,
                'tecnicos' => User::where('unidad_id', $miUnidadId)->where('activo', true)->get(),
            ])->render(),
            'mis_asignados' => view('partials.filas_mis_asignados', ['misAsignados' => $ticketsResult])->render(),
        };
    }
}
