<?php

namespace App\Http\Controllers;

use App\Models\Manual;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class ApiTableController extends Controller
{
    //------------LIMITACIONES-----------
    private const TIPOS_VALIDOS = ['dashboard', 'usuario', 'asignar', 'mis_tickets', 'mis_asignados', 'historial'];
    private const TIPOS_SOLO_CONTENIDO = ['recursos'];
    private const TIPOS_SOLO_STAFF = ['dashboard', 'asignar', 'historial', 'mis_asignados'];
    private const ESTADOS_CERRADOS = [3, 4, 5];

    //-----------------METODO REFRESH----------------
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
            //          QUERY TABLA
            // =================================
            $queryTickets = Ticket::with(['user.unidad', 'estado', 'prioridad', 'tecnico', 'tipo_solicitud', 'categoria']);

            $this->aplicarFiltrosTabla($queryTickets, $user, $tipo, $miUnidadId, $estadoFiltro);
            //----Dashboard usuario solo 5 registros
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
            //----grafico
            $añoActual = (int) date('Y');
            $graficoHtml = ($user->rol_id != 2 && $tipo === 'dashboard') ? $this->generarGrafico($miUnidadId, $añoActual) : null;
            $contadorMisAsignados = Ticket::where('tecnico_id', $user->id)->where('estado_id', 2)->count();
            //----carga de trabajo
            [$cargaTrabajo, $resueltos24h, $tasaCierre] = ($tipo === 'historial')
                ? $this->calcularMetricasHistorial($user, $miUnidadId, $añoActual)
                : [0, 0, 0];

            $estadosCerrados = [3, 4, 5];
            $queryPrioridades = Ticket::whereNotIn('estado_id', $estadosCerrados);

            if ($miUnidadId) {
                $queryPrioridades->whereHas('categoria', fn($q) => $q->where('unidad_id', $miUnidadId));
            }
            //-------prioridades: un solo GROUP BY en vez de 4 counts separados
            $conteosPorPrioridad = $queryPrioridades
                ->selectRaw('prioridad_id, COUNT(*) as total')
                ->groupBy('prioridad_id')
                ->pluck('total', 'prioridad_id');

            $prioridades = [
                'critica' => (int) ($conteosPorPrioridad[1] ?? 0),
                'alta'    => (int) ($conteosPorPrioridad[2] ?? 0),
                'media'   => (int) ($conteosPorPrioridad[3] ?? 0),
                'baja'    => (int) ($conteosPorPrioridad[4] ?? 0),
            ];

            //-----------RESPUESTA JSON----------
            return response()->json([
                'html'              => $this->renderizarVista($tipo, $ticketsResult, $miUnidadId),
                'contadores'        => $contadores,
                'prioridades'       => $prioridades,
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
     * Filtros de estados                                                                                                                                                                STADOS para las tablas
     */
    private function aplicarFiltrosTabla($query, $user, string $tipo, $miUnidadId, string $estadoFiltro): void
    {
        //-------El cliente solo observa lo que le corresponde
        if ($tipo === 'usuario' || $tipo === 'mis_tickets') {
            $query->where('user_id', $user->id)
                ->where(function ($q) {
                    $q->whereYear('created_at', date('Y'))
                        ->orWhereNotIn('estado_id', self::ESTADOS_CERRADOS);
                });

            if ($estadoFiltro === 'todos' || $estadoFiltro === '') {
                return;
            }
            if ($estadoFiltro === 'resuelto,equivocado,no corresponde' || $estadoFiltro === 'cerrado') {
                $query->whereIn('estado_id', self::ESTADOS_CERRADOS);
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
                    //---cuenta por mes de cierre, no de apertura (igual que el card de "resueltos")
                    $query->whereIn('estado_id', self::ESTADOS_CERRADOS)
                        ->whereMonth('fecha_cierre', date('m'))
                        ->whereYear('fecha_cierre', date('Y'));
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
        $mesActual = (int) date('m');
        $añoActual = (int) date('Y');
        $esCliente = $user->rol_id == 2 || $tipo === 'usuario' || $tipo === 'mis_tickets';
        $columnaResueltos = $esCliente ? 'created_at' : 'fecha_cierre';

        //--------un solo query con SUM(CASE...) en vez de 3 counts separados
        $expresionContadores = "
            SUM(CASE WHEN tecnico_id IS NULL AND estado_id NOT IN (3,4,5) THEN 1 ELSE 0 END) as abiertos,
            SUM(CASE WHEN tecnico_id IS NOT NULL AND estado_id = 2 THEN 1 ELSE 0 END) as proceso,
            SUM(CASE WHEN estado_id IN (3,4,5) AND MONTH({$columnaResueltos}) = ? AND YEAR({$columnaResueltos}) = ? THEN 1 ELSE 0 END) as resueltos
        ";

        if ($esCliente) {
            $fila = Ticket::where('user_id', $user->id)
                ->selectRaw($expresionContadores, [$mesActual, $añoActual])
                ->first();
        } else {
            $query = Ticket::query();

            //--------restringe contadores por unidad
            if ($miUnidadId) {
                $query->whereHas('categoria', fn($q) => $q->where('unidad_id', $miUnidadId));
            }

            $fila = $query->selectRaw($expresionContadores, [$mesActual, $añoActual])->first();
        }

        return [
            'abiertos'  => (int) ($fila->abiertos ?? 0),
            'proceso'   => (int) ($fila->proceso ?? 0),
            'resueltos' => (int) ($fila->resueltos ?? 0),
        ];
    }

    /**
     * Renderizador del gráfico mensual basado en index() de tus controladores staff
     */
    private function generarGrafico($miUnidadId, int $año): ?string
    {
        $nombresMeses = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];

        //----ambos controladores agrupan la gráfica obligatoriamente pasando la variable de unidad del usuario autenticado
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

        //---metricas agregadas en una sola consulta, en vez de traer todos los tickets del año a PHP
        $hoy = now()->toDateString();
        $hace24h = now()->subDay()->toDateTimeString();
        $mesActual = (int) now()->format('m');
        $añoActual = (int) now()->format('Y');

        $fila = $query->selectRaw(
            'SUM(CASE WHEN DATE(created_at) = ? THEN 1 ELSE 0 END) as carga_trabajo,
             SUM(CASE WHEN estado_id IN (3,4,5) AND fecha_cierre IS NOT NULL AND fecha_cierre >= ? THEN 1 ELSE 0 END) as resueltos_24h,
             SUM(CASE WHEN MONTH(created_at) = ? AND YEAR(created_at) = ? THEN 1 ELSE 0 END) as total_mes,
             SUM(CASE WHEN MONTH(created_at) = ? AND YEAR(created_at) = ? AND estado_id IN (3,4,5) THEN 1 ELSE 0 END) as cerrados_mes',
            [$hoy, $hace24h, $mesActual, $añoActual, $mesActual, $añoActual]
        )->first();

        $cargaTrabajo = (int) ($fila->carga_trabajo ?? 0);
        $resueltos24h = (int) ($fila->resueltos_24h ?? 0);
        $totalMes     = (int) ($fila->total_mes ?? 0);
        $cerradosMes  = (int) ($fila->cerrados_mes ?? 0);
        $tasaCierre   = $totalMes > 0 ? (int) round(($cerradosMes / $totalMes) * 100) : 0;

        return [$cargaTrabajo, $resueltos24h, $tasaCierre];
    }

    //----------OBTIENE TODAS LAS VISTAS
    private function renderizarVista(string $tipo, $ticketsResult, $miUnidadId): string
    {
        //------obtener tecnicos
        $tecnicos = [];
        if (in_array($tipo, ['asignar', 'mis_asignados']) && $miUnidadId) {
            $tecnicos = User::where('unidad_id', $miUnidadId)
                ->where('activo', true)
                ->whereHas('rol', fn($q) => $q->whereIn('nombre_rol', ['Admin', 'Gestor']))
                ->get();
        }

        //---------renderiza segun el tipo de vista
        return match ($tipo) {
            'dashboard'     => view('partials.filas_dashboard', ['todosLosTickets' => $ticketsResult])->render(),
            'usuario'       => view('partials.filas_usuario', ['todosLosTickets' => $ticketsResult])->render(),
            'mis_tickets'   => view('partials.filas_mis_tickets', ['misTickets' => $ticketsResult])->render(),
            'historial'     => view('partials.filas_historial', ['tickets' => $ticketsResult])->render(),
            'asignar'       => view('partials.filas_asignar', ['tickets' => $ticketsResult, 'tecnicos' => $tecnicos])->render(),
            'mis_asignados' => view('partials.filas_mis_asignados', ['tickets' => $ticketsResult, 'tecnicos' => $tecnicos])->render(),
            default => '',
        };
    }
}
