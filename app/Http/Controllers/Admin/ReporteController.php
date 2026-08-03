<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ExportaReporteTickets;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    use ExportaReporteTickets;

    public function exportar(Request $request)
    {
        return $this->generarReporteTickets($request, 'admin.reportes.pdf_historial', 'admin.historial');
    }
}
