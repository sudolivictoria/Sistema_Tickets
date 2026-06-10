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
        //----evitar sesiones concurrentes
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $tipo = (string) $request->query('tipo', 'dashboard');
        if (!in_array($tipo, self::TIPOS_VALIDOS, true) && !in_array($tipo, self::TIPOS_SOLO_CONTENIDO, true)) {
            return response()->json(['error' => 'Tipo no válido.'], 422);
        }
        //---------obtener la unidad del usaurio
        $miUnidadId = $user->unidad_id;

        if (in_array($tipo, self::TIPOS_SOLO_CONTENIDO, true)) {
            return response()->json(['html' => $this->renderizarVista($tipo, collect(), $miUnidadId)]);
        }

        if ($user->rol_id == 2 && in_array($tipo, self::TIPOS_SOLO_STAFF, true)) {
            return response()->json(['error' => 'Acceso denegado.'], 403);
        }

        try {
            $estadoFiltro = strtolower(trim((string) $request->query('estado', 'todos')));

            // =================================
            //    QUERY EXACTA PARA LA TABLA
            // =================================
            $queryTickets = Ticket::with(['user.unidad', 'estado', 'prioridad', 'tecnico', 'tipo_solicitud', 'categoria']);

            $this->aplicarFiltrosTabla($queryTickets, $user, $tipo, $miUnidadId, $estadoFiltro);

            $limit = ($tipo === 'usuario') ? 5 : null;
            $ticketsResult = $limit
                ? $queryTickets->latest()->take($limit)->get()
                : $queryTickets->latest()->get();

            // =========================================================
            //     CONTADORES SUPERIORES (MÉTRICAS DE TARJETAS)
            // =========================================================
            $contadores = $this->calcularContadores($user, $tipo, $miUnidadId);

            // =========================================================
            //    GRÁFICOS Y MÉTRICAS EXTRA (SÓLO SI CORRESPONDE)
            // =========================================================
            $añoActual = (int) date('Y');
            $graficoHtml = ($user->rol_id != 2 && $tipo === 'dashboard') ? $this->generarGrafico($miUnidadId, $añoActual) : null;
            $contadorMisAsignados = Ticket::where('tecnico_id', $user->id)->where('estado_id', 2)->count();

            [$cargaTrabajo, $resueltos24h, $tasaCierre] = ($tipo === 'historial')
                ? $this->calcularMetricasHistorial($user, $miUnidadId, $añoActual)
                : [0, 0, 0];

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
            Log::error('ApiTableController Refresh Error: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno.'], 500);
        }
    }

    /**
     * Filtros de tablas extraídos fielmente de la estructura de tus controladores
     */
    private function aplicarFiltrosTabla($query, $user, string $tipo, $miUnidadId, string $estadoFiltro): void
    {
        //-------El cliente solo observa lo que le corresponde
        if ($tipo === 'usuario' || $tipo === 'mis_tickets') {
            $query->where('user_id', $user->id); //-----encapsula la consulta al usuario autenticado (Cliente, Admin o Gestor por igual)

            if ($estadoFiltro === 'todos' || $estadoFiltro === '') {
                return;
            }

            if ($estadoFiltro === 'resuelto,equivocado,no corresponde' || $estadoFiltro === 'cerrado') {
                $query->whereIn('estado_id', self::ESTADOS_CERRADOS)
                    ->whereMonth('created_at', date('m'))
                    ->whereYear('created_at', date('Y'));
            } else {
                if ($estadoFiltro === 'abierto' || $estadoFiltro === '1') {
                    $query->whereNull('tecnico_id')->whereNotIn('estado_id', self::ESTADOS_CERRADOS);
                } elseif ($estadoFiltro === 'procesando' || $estadoFiltro === '2') {
                    $query->whereNotNull('tecnico_id')->where('estado_id', 2);
                } else {
                    $query->whereNotIn('estado_id', self::ESTADOS_CERRADOS);
                }
            }
            return;
        }
        //-----------tipos de tablas
        switch ($tipo) {
            case 'dashboard':
                if ($estadoFiltro === 'resuelto,equivocado,no corresponde' || $estadoFiltro === 'cerrado') {
                    $query->whereIn('estado_id', self::ESTADOS_CERRADOS)
                        ->whereMonth('created_at', date('m'))
                        ->whereYear('created_at', date('Y'));
                } else {
                    if ($estadoFiltro === 'abierto' || $estadoFiltro === '1') {
                        $query->whereNull('tecnico_id')->whereNotIn('estado_id', self::ESTADOS_CERRADOS);
                    } elseif ($estadoFiltro === 'procesando' || $estadoFiltro === '2') {
                        $query->whereNotNull('tecnico_id')->where('estado_id', 2);
                    } else {
                        $query->whereNotIn('estado_id', self::ESTADOS_CERRADOS);
                    }
                }

                //----solo filtra lo de cada unidad
                if ($miUnidadId) {
                    $query->whereHas('categoria', fn($q) => $q->where('unidad_id', $miUnidadId));
                }
                break;

            case 'asignar':
                $query->where('estado_id', 1);
                $query->whereHas('categoria', fn($q) => $q->where('unidad_id', $miUnidadId));
                break;

            case 'mis_asignados':
                $query->where('tecnico_id', $user->id)->where('estado_id', 2);
                break;

            case 'historial':
                $query->whereYear('created_at', date('Y'));
                //---Admin controller global y admin unidad contraller solo lo de su unidad
                if ($user->rol_id == 3 && $miUnidadId) {
                    $query->whereHas('categoria', fn($q) => $q->where('unidad_id', $miUnidadId));
                }
                break;
        }
    }

    /**
     * Sincronización de contadores del cuadro estadístico superior del dashboard
     */
    private function calcularContadores($user, string $tipo, $miUnidadId): array
    {
        if ($user->rol_id == 2 || $tipo === 'usuario' || $tipo === 'mis_tickets') {
            return [
                'abiertos'  => Ticket::where('user_id', $user->id)
                    ->whereNull('tecnico_id')
                    ->whereNotIn('estado_id', self::ESTADOS_CERRADOS)->count(),
                'proceso'   => Ticket::where('user_id', $user->id)
                    ->whereNotNull('tecnico_id')
                    ->where('estado_id', 2)->count(),
                'resueltos' => Ticket::where('user_id', $user->id)
                    ->whereIn('estado_id', self::ESTADOS_CERRADOS)
                    ->whereMonth('created_at', date('m'))
                    ->whereYear('created_at', date('Y'))->count(),
            ];
        }

        $queryAbiertos = Ticket::whereNull('tecnico_id')->whereNotIn('estado_id', self::ESTADOS_CERRADOS);
        $queryProceso = Ticket::whereNotNull('tecnico_id')->where('estado_id', 2);
        $queryResueltos = Ticket::whereIn('estado_id', self::ESTADOS_CERRADOS)
            ->whereMonth('created_at', date('m'))
            ->whereYear('created_at', date('Y'));

        //--------restringe contadores por unidad
        if ($miUnidadId) {
            $filterUnidad = fn($q) => $q->where('unidad_id', $miUnidadId);
            $queryAbiertos->whereHas('categoria', $filterUnidad);
            $queryProceso->whereHas('categoria', $filterUnidad);
            $queryResueltos->whereHas('categoria', $filterUnidad);
        }

        return [
            'abiertos'  => $queryAbiertos->count(),
            'proceso'   => $queryProceso->count(),
            'resueltos' => $queryResueltos->count(),
        ];
    }

    /**
     * Renderizador del gráfico mensual basado en index() de tus controladores staff
     */
    private function generarGrafico($miUnidadId, int $año): ?string
    {
        $nombresMeses = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];

        // Ambos controladores agrupan la gráfica obligatoriamente pasando la variable de unidad del usuario autenticado
        $statsMensuales = Ticket::selectRaw('MONTH(created_at) as mes, estado_id, COUNT(*) as total')
            ->whereYear('created_at', $año)
            ->whereHas('categoria', fn($q) => $q->where('unidad_id', $miUnidadId))
            ->groupBy('mes', 'estado_id')
            ->get();

        $mesesGrafico = [];
        for ($i = 1; $i <= 12; $i++) {
            $res   = $statsMensuales->where('mes', $i)->whereIn('estado_id', self::ESTADOS_CERRADOS)->sum('total');
            $pen   = $statsMensuales->where('mes', $i)->whereNotIn('estado_id', self::ESTADOS_CERRADOS)->sum('total');
            $total = $res + $pen;

            $mesesGrafico[] = [
                'nombre'         => $nombresMeses[$i - 1],
                'resueltos_pct'  => $total > 0 ? (int) round(($res / $total) * 100) : 0,
                'pendientes_pct' => $total > 0 ? (int) round(($pen / $total) * 100) : 0,
                'total'          => $total,
            ];
        }
        return view('partials.grafico_rendimiento', compact('mesesGrafico'))->render();
    }

    /**
     * Métricas del historial alineadas con la visibilidad del rol
     */
    private function calcularMetricasHistorial($user, $miUnidadId, int $año): array
    {
        $query = Ticket::whereYear('created_at', $año);

        //----Gestor solo observa metricas de su unidad y admin a nivel global
        if ($user->rol_id == 3 && $miUnidadId) {
            $query->whereHas('categoria', fn($q) => $q->where('unidad_id', $miUnidadId));
        }
        $tickets = $query->get(['id', 'estado_id', 'created_at', 'fecha_cierre']);

        $cargaTrabajo = $tickets->filter(fn($t) => Carbon::parse($t->created_at)->isToday())->count();
        $resueltos24h = $tickets->whereIn('estado_id', self::ESTADOS_CERRADOS)
            ->filter(fn($t) => $t->fecha_cierre && Carbon::parse($t->fecha_cierre)->gte(now()->subDay()))
            ->count();
        $delMes     = $tickets->filter(fn($t) => Carbon::parse($t->created_at)->isCurrentMonth());
        $total      = $delMes->count();
        $cerrados   = $delMes->whereIn('estado_id', self::ESTADOS_CERRADOS)->count();
        $tasaCierre = $total > 0 ? (int) round(($cerrados / $total) * 100) : 0;

        return [$cargaTrabajo, $resueltos24h, $tasaCierre];
    }

    //----------OBTIENE TODAS LAS VISTAS
    private function renderizarVista(string $tipo, $ticketsResult, $miUnidadId): string
    {
        $tecnicos = [];
        if (in_array($tipo, ['asignar', 'mis_asignados']) && $miUnidadId) {
            $tecnicos = User::where('unidad_id', $miUnidadId)->where('activo', true)->get();
        }

        return match ($tipo) {
            'dashboard'     => view('partials.filas_dashboard', ['todosLosTickets' => $ticketsResult])->render(),
            'usuario'       => view('partials.filas_usuario', ['todosLosTickets' => $ticketsResult])->render(),
            'mis_tickets'   => view('partials.filas_mis_tickets', ['misTickets' => $ticketsResult])->render(),
            'historial'     => view('partials.filas_historial', ['tickets' => $ticketsResult])->render(),
            'recursos'      => view('partials.filas_recursos', ['manuales' => Manual::with('categoria')->latest()->get()])->render(),
            'asignar'       => view('partials.filas_asignar', ['tickets' => $ticketsResult, 'tecnicos' => $tecnicos])->render(),
            'mis_asignados' => view('partials.filas_mis_asignados', ['tickets' => $ticketsResult, 'tecnicos' => $tecnicos])->render(),
            default => '',
        };
    }
}
