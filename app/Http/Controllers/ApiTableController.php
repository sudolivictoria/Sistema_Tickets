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
    // ======================================
    // CONSTANTES DE CONFIGURACIÓN
    // ======================================

    //---tablas
    private const TIPOS_VALIDOS = [
        'dashboard',
        'usuario',
        'asignar',
        'mis_tickets',
        'mis_asignados',
        'historial',
    ];
    //-----contenido 
    private const TIPOS_SOLO_CONTENIDO = ['recursos'];
    /*---usuario normal*/
    private const TIPOS_SOLO_STAFF = ['asignar', 'historial', 'mis_asignados'];
    /*estados cerrados*/
    private const ESTADOS_CERRADOS = [3, 4, 5];

    // =====================
    // ENDPOINT PRINCIPAL
    // =====================
    public function refresh(Request $request): JsonResponse
    {
        // ──Autenticación ─────
        $user = Auth::user();
        if (!$user) {
            return $this->errorJson('No autenticado.', 401);
        }
        // ─Validar y sanitizar $tipo ───
        $tipo = (string) $request->query('tipo', 'dashboard');

        $tipoPermitido = in_array($tipo, self::TIPOS_VALIDOS, true)
            || in_array($tipo, self::TIPOS_SOLO_CONTENIDO, true);

        if (!$tipoPermitido) {
            return $this->errorJson('Tipo no válido.', 422);
        }

        //---recursos
        if (in_array($tipo, self::TIPOS_SOLO_CONTENIDO, true)) {
            try {
                $html = $this->renderizarVista($tipo, collect(), 0);
                return response()->json(['html' => $html]);
            } catch (Throwable $e) {
                Log::error('ApiTableController: error en tipo contenido', ['tipo' => $tipo, 'error' => $e->getMessage()]);
                return $this->errorJson('Error interno.', 500);
            }
        }

        // ──control de acceso por rol ───
        if ($user->rol_id == 2 && in_array($tipo, self::TIPOS_SOLO_STAFF, true)) {
            return $this->errorJson('Acceso denegado para tu rol.', 403);
        }

        // ── captura de errores en construcción de respuesta ───
        try {
            return $this->construirRespuesta($request, $user, $tipo);
        } catch (Throwable $e) {
            Log::error('ApiTableController::refresh falló', [
                'tipo'    => $tipo,
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return $this->errorJson('Error interno al procesar la solicitud.', 500);
        }
    }

    //====================
    // LÓGICA PRINCIPAL
    //====================

    private function construirRespuesta(Request $request, $user, string $tipo): JsonResponse
    {
        $miUnidadId = (int) $user->unidad_id;
        $añoActual  = (int) date('Y');

        //──Query principal de tickets────────────
        $queryTickets = Ticket::with([
            'user.unidad',
            'estado',
            'prioridad',
            'tecnico',
            'tipo_solicitud',
            'categoria',
        ]);
        $this->aplicarFiltros($queryTickets, $user, $tipo, $miUnidadId);
        $ticketsResult = $queryTickets->latest()->get();

        // ── Contadores anuales ─────
        $statsBase = function () use ($añoActual, $user, $tipo, $miUnidadId) {
            $q = Ticket::whereYear('created_at', $añoActual);
            $this->aplicarFiltrosStats($q, $user, $tipo, $miUnidadId);
            return $q;
        };

        $contadores = [
            'abiertos'  => $statsBase()
                ->whereNull('tecnico_id')
                ->whereNotIn('estado_id', self::ESTADOS_CERRADOS)
                ->count(),

            'proceso'   => $statsBase()
                ->whereNotNull('tecnico_id')
                ->where('estado_id', 2)
                ->count(),

            'resueltos' => $statsBase()
                ->whereIn('estado_id', self::ESTADOS_CERRADOS)
                ->count(),
        ];

        // ── Gráfico ─────────────────
        $graficoHtml = null;
        if ($user->rol_id != 2) {
            $graficoHtml = $this->generarGrafico($miUnidadId, $añoActual);
        }

        // ── Contador mis asignados ────────────────────────────
        $contadorMisAsignados = Ticket::where('tecnico_id', $user->id)
            ->where('estado_id', 2)
            ->count();

        // ── Métricas (solo historial) ─────────────────────────
        [$cargaTrabajo, $resueltos24h, $tasaCierre] = $tipo === 'historial'
            ? $this->calcularMetricas($user, $miUnidadId, $añoActual)
            : [0, 0, 0];

        // ── HTML de la tabla ──────────────────────────────────
        $html = $this->renderizarVista($tipo, $ticketsResult, $miUnidadId);

        return response()->json([
            'html'              => $html,
            'contadores'        => $contadores,
            'grafico'           => $graficoHtml,
            'contadorAsignados' => (int) $contadorMisAsignados,
            'cargaTrabajo'      => (int) $cargaTrabajo,
            'resueltos24h'      => (int) $resueltos24h,
            'tasaCierre'        => (int) $tasaCierre,
        ]);
    }

    // ===========================
    // FILTROS
    // ==========================
    private function aplicarFiltros($query, $user, string $tipo, int $miUnidadId): void
    {
        if ($tipo === 'mis_tickets' || $user->rol_id == 2) {
            $query->where('user_id', $user->id);
            return;
        }
        if ($tipo === 'mis_asignados') {
            $query->where('tecnico_id', $user->id);
            return;
        }
        if ($tipo === 'historial') {
            if ($user->rol_id != 1) {
                $query->whereHas('categoria', fn($q) => $q->where('unidad_id', $miUnidadId));
            }
        } else {
            $query->whereHas('categoria', fn($q) => $q->where('unidad_id', $miUnidadId));
        }
        if ($tipo === 'asignar') {
            $query->where('estado_id', 1);
        }
        if ($tipo === 'usuario') {
            $query->limit(5);
        }
        if ($tipo === 'dashboard') {
            $query->whereBetween('created_at', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ]);
        }
    }
    /**
     * Filtros para las queries
     */
    private function aplicarFiltrosStats($query, $user, string $tipo, int $miUnidadId): void
    {
        if ($tipo === 'mis_tickets' || $user->rol_id == 2) {
            $query->where('user_id', $user->id);
            return;
        }

        if ($tipo === 'mis_asignados') {
            $query->where('tecnico_id', $user->id);
            return;
        }

        //----Admin ve todo el sistema en historial, pero en dashboard solo su unidad
        if ($tipo === 'historial' && $user->rol_id == 1) {
            return;
        }
        $query->whereHas('categoria', fn($q) => $q->where('unidad_id', $miUnidadId));
    }
    // ============
    // GRÁFICO
    // ============
    private function generarGrafico(int $miUnidadId, int $año): ?string
    {
        $nombresMeses = [
            'ENE',
            'FEB',
            'MAR',
            'ABR',
            'MAY',
            'JUN',
            'JUL',
            'AGO',
            'SEP',
            'OCT',
            'NOV',
            'DIC'
        ];
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

    // ============METRICAS===============
    private function calcularMetricas($user, int $miUnidadId, int $año): array
    {
        $query = Ticket::whereYear('created_at', $año);

        if ($user->rol_id != 1) {
            $query->whereHas('categoria', fn($q) => $q->where('unidad_id', $miUnidadId));
        }

        //----solo columnas necesarias-----------
        $tickets = $query->get(['id', 'estado_id', 'created_at', 'fecha_cierre']);

        $cargaTrabajo = $tickets
            ->filter(fn($t) => Carbon::parse($t->created_at)->isToday())
            ->count();

        $resueltos24h = $tickets
            ->whereIn('estado_id', self::ESTADOS_CERRADOS)
            ->filter(fn($t) => $t->fecha_cierre &&
                Carbon::parse($t->fecha_cierre)->gte(now()->subDay()))
            ->count();

        $delMes     = $tickets->filter(fn($t) => Carbon::parse($t->created_at)->isCurrentMonth());
        $total      = $delMes->count();
        $cerrados   = $delMes->whereIn('estado_id', self::ESTADOS_CERRADOS)->count();
        $tasaCierre = $total > 0 ? (int) round(($cerrados / $total) * 100) : 0;

        return [$cargaTrabajo, $resueltos24h, $tasaCierre];
    }

    // ==========renderizado vistas==============

    private function renderizarVista(string $tipo, $ticketsResult, int $miUnidadId): string
    {
        return match ($tipo) {
            'dashboard'   => view(
                'partials.filas_dashboard',
                ['todosLosTickets' => $ticketsResult]
            )->render(),

            'usuario'     => view(
                'partials.filas_usuario',
                ['todosLosTickets' => $ticketsResult]
            )->render(),

            'mis_tickets' => view(
                'partials.filas_mis_tickets',
                ['misTickets' => $ticketsResult]
            )->render(),

            'historial'   => view(
                'partials.filas_historial',
                ['tickets' => $ticketsResult]
            )->render(),

            'recursos'    => view('partials.filas_recursos', [
                'manuales' => Manual::with('categoria')->latest()->get(),
            ])->render(),

            'asignar', 'mis_asignados' => view("partials.filas_{$tipo}", [
                'tickets'  => $ticketsResult,
                'tecnicos' => User::where('unidad_id', $miUnidadId)
                    ->where('activo', true)
                    ->get(),
            ])->render(),

            default => '',
        };
    }

    private function errorJson(string $mensaje, int $status): JsonResponse
    {
        return response()->json(['error' => $mensaje], $status);
    }
}
