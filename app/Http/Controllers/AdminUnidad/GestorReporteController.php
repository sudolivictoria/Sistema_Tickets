<?php

namespace App\Http\Controllers\AdminUnidad;

use App\Http\Controllers\Concerns\ExportaReporteTickets;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GestorReporteController extends Controller
{
    use ExportaReporteTickets;

    public function exportar(Request $request)
    {
        return $this->generarReporteTickets($request, 'gestor.reportes.pdf_historial', 'gestor.historial');
    }
}
