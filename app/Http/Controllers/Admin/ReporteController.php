<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    public function exportar(Request $request)
    {
        try {
            $query = Ticket::with(['user', 'tecnico', 'estado', 'categoria']);

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

            //-----registros ordenados
            $tickets = $query->latest()->get();
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

                $callback = function () use ($tickets, $columnas) {
                    $file = fopen('php://output', 'w');
                    //---UTF-8 para Excel
                    fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

                    //------CABECERAS
                    fputcsv($file, $columnas, ';');

                    foreach ($tickets as $ticket) {
                        $asuntoClean = str_replace(["\r", "\n", ";"], [" ", " ", " "], $ticket->asunto ?? '');
                        $descClean = str_replace(["\r", "\n", ";"], [" ", " ", " "], $ticket->descripcion ?? '');

                        $tiempoRespuestaFormateado = '-------';
                        
                        if ($ticket->tiempo_respuesta !== null && $ticket->tiempo_respuesta !== '') {
                            $totalSegundos = (int)$ticket->tiempo_respuesta;
                            
                            $dias = floor($totalSegundos / 86400);
                            $horas = floor(($totalSegundos % 86400) / 3600);
                            $minutos = floor(($totalSegundos % 3600) / 60);
                            $segundos = $totalSegundos % 60;

                            $piezas = [];
                            if ($dias > 0) $piezas[] = "{$dias}d";
                            if ($horas > 0) $piezas[] = "{$horas}h";
                            if ($minutos > 0) $piezas[] = "{$minutos}m";
                            
                            if (empty($piezas)) {
                                $piezas[] = "{$segundos}s";
                            }

                            $tiempoRespuestaFormateado = implode(' ', $piezas);
                        }

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
                            $ticket->created_at->format('d/m/Y'),
                            $ticket->fecha_cierre ? date('d/m/Y', strtotime($ticket->fecha_cierre)) : '-------',
                            $tiempoRespuestaFormateado 
                        ], ';');
                    }
                    fclose($file);
                };

                return response()->stream($callback, 200, $headers);
            }

            //-------PDF
            if ($formato === 'pdf') {
                $pdf = Pdf::loadView('admin.reportes.pdf_historial', compact('tickets'))
                    ->setPaper('letter', 'landscape');
                return $pdf->stream('reporte_historial_' . date('d-m-Y_His') . '.pdf');
            }
        } catch (\Exception $e) {
            return redirect()->route('admin.historial')
                ->with('error', 'Ocurrió un error al generar el reporte.');
        }
    }
}