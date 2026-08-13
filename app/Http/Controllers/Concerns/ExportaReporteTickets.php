<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

/**
 *lógica de exportación de reportes (Excel/PDF) compartida entre
 * ReporteController (Admin) y GestorReporteController (Gestor) — antes duplicada
 */
trait ExportaReporteTickets
{
    //----------------------------metodo para formatear tiempo de respuesta
    private function formatearTiempoRespuesta($tiempoRespuesta): string
    {
        if ($tiempoRespuesta === null || $tiempoRespuesta === '') {
            return '-------';
        }
        $totalSegundos = (int) $tiempoRespuesta;

        $dias = intdiv($totalSegundos, 86400);
        $restoSegundos = $totalSegundos % 86400;

        $horas = intdiv($restoSegundos, 3600);
        $restoSegundos %= 3600;

        $minutos = intdiv($restoSegundos, 60);
        $segundos = $restoSegundos % 60;

        $pad = fn($num) => sprintf('%02d', $num);

        if ($dias > 0) {
            return "{$dias}d {$pad($horas)}h {$pad($minutos)}m {$pad($segundos)}s";
        } elseif ($horas > 0) {
            return "{$pad($horas)}h {$pad($minutos)}m {$pad($segundos)}s";
        } elseif ($minutos > 0) {
            return "{$pad($minutos)}m {$pad($segundos)}s";
        }
        return "{$pad($segundos)}s";
    }

    /**
     * @param  string  $vistaPdf  Nombre de la vista Blade del PDF (ej. 'admin.reportes.pdf_historial')
     * @param  string  $rutaRedirectError  Nombre de ruta a la que redirigir si falla (ej. 'admin.historial')
     */
    private function generarReporteTickets(Request $request, string $vistaPdf, string $rutaRedirectError)
    {
        try {
            //----Eager Loading completo para optimizar las consultas a la base de datos
            $query = Ticket::with(['user.unidad', 'tecnico', 'estado', 'categoria', 'tipo_solicitud', 'prioridad']);

            //----el Gestor solo exporta tickets de su propia unidad (evita fuga de datos entre unidades);
            //----el Admin exporta acorde a lo que ve en la tabla, sin restricción de unidad
            $usuarioActual = auth()->user();
            $miUnidadId = $usuarioActual?->unidad_id;
            if ($miUnidadId && $usuarioActual->tieneRol('Gestor')) {
                $query->whereHas('categoria', fn($q) => $q->where('unidad_id', $miUnidadId));
            }

            //------Búsqueda general por ID, nombre de usuario o técnico
            if ($request->filled('buscar')) {
                $buscar = $request->input('buscar');
                $query->where(function ($q) use ($buscar) {
                    $q->where('id', $buscar)
                        ->orWhereHas('user', function ($u) use ($buscar) {
                            $u->where('name', 'like', "%{$buscar}%");
                        })
                        ->orWhereHas('tecnico', function ($t) use ($buscar) {
                            $t->where('name', 'like', "%{$buscar}%");
                        });
                });
            }

            //---fecha inicio
            if ($request->filled('fecha_inicio')) {
                $query->whereDate('created_at', '>=', $request->input('fecha_inicio'));
            }

            //----fecha fin
            if ($request->filled('fecha_fin')) {
                $query->whereDate('created_at', '<=', $request->input('fecha_fin'));
            }

            //-----filtrar por estado
            if ($request->filled('estado') && $request->input('estado') !== 'todos') {
                $query->where('estado_id', $request->input('estado'));
            }

            //------filtrar por categoria
            if ($request->filled('categoria') && $request->input('categoria') !== 'todos') {
                $query->where('categoria_id', $request->input('categoria'));
            }

            $formato = $request->input('tipo', 'excel');

            //------------------------------EXCEL / CSV-----------------------------------------------------
            if ($formato === 'excel') {
                $headers = [
                    "Content-type"        => "text/csv; charset=UTF-8",
                    "Content-Disposition" => "attachment; filename=reporte_historial_" . date('d-m-Y_His') . ".csv",
                    "Pragma"              => "no-cache",
                    "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                    "Expires"             => "0"
                ];

                $columnas = [
                    'ID',
                    'Usuario',
                    'Unidad del Usuario',
                    'Categoría',
                    'Tipo de Solicitud',
                    'Prioridad',
                    'Estado',
                    'Asunto',
                    'Descripción',
                    'Técnico',
                    'Apertura',
                    'Cierre',
                    'Tiempo de Respuesta',
                ];

                $callback = function () use ($query, $columnas) {
                    $file = fopen('php://output', 'w');

                    fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

                    fputcsv($file, $columnas, ';');

                    //----consulta lazy
                    $query->lazy()->each(function ($ticket) use ($file) {
                        $asuntoClean = str_replace(["\r", "\n", ";"], [" ", " ", " "], $ticket->asunto ?? '');
                        $descClean = str_replace(["\r", "\n", ";"], [" ", " ", " "], $ticket->descripcion ?? '');

                        $tiempoRespuestaFormateado = $this->formatearTiempoRespuesta($ticket->tiempo_respuesta);

                        fputcsv($file, [
                            'TK' . str_pad($ticket->id, 5, '0', STR_PAD_LEFT),
                            $ticket->user->name ?? '',
                            $ticket->user->unidad->nombre_unidad ?? '',
                            $ticket->categoria->nombre_categoria ?? '',
                            $ticket->tipo_solicitud->nombre_tipo_solicitud ?? '',
                            $ticket->prioridad->nombre_prioridad ?? '',
                            $ticket->estado->nombre_estado ?? '',
                            $asuntoClean,
                            $descClean,
                            $ticket->tecnico->name ?? 'No asignado',
                            $ticket->created_at ? $ticket->created_at->format('d/m/Y') : '-------',
                            $ticket->fecha_cierre ? date('d/m/Y', strtotime($ticket->fecha_cierre)) : '-------',
                            $tiempoRespuestaFormateado
                        ], ';');
                    });

                    fclose($file);
                };

                return response()->stream($callback, 200, $headers);
            }

            //-------PDF
            if ($formato === 'pdf') {
                $tickets = $query->get();

                $tickets->each(function ($ticket) {
                    $ticket->tiempo_respuesta_formateado = $this->formatearTiempoRespuesta($ticket->tiempo_respuesta);
                });

                $pdf = Pdf::loadView($vistaPdf, compact('tickets'))
                    ->setPaper('letter', 'landscape');
                return $pdf->stream('reporte_historial_' . date('d-m-Y_His') . '.pdf');
            }
        } catch (\Exception $e) {
            return redirect()->route($rutaRedirectError)
                ->with('error', 'Ocurrió un error al generar el reporte.');
        }
    }
}
