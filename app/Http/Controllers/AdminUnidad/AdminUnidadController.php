<?php

namespace App\Http\Controllers\AdminUnidad;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Manual;
use App\Models\Prioridad;
use App\Models\Ticket;
use App\Models\TipoSolicitud;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminUnidadController extends Controller
{
    public function index()
    {
        $miUnidadId = Auth::user()->unidad_id;

        //--tickets asignados por unidad del admin autenticado
        $noAsignados = Ticket::whereNull('tecnico_id')
            ->where('estado_id', '!=', 3)
            ->whereHas('categoria', function ($q) use ($miUnidadId) {
                $q->where('unidad_id', $miUnidadId);
            })
            ->count();

        //--tickets pendientes por unidad del admin autenticado
        $pendientes = Ticket::whereNotNull('tecnico_id')
            ->where('estado_id', '!=', 3)
            ->whereHas('categoria', function ($q) use ($miUnidadId) {
                $q->where('unidad_id', $miUnidadId);
            })
            ->count();

        //--tickets resueltos por unidad del admin autenticado
        $resueltos = Ticket::where('estado_id', '=', 3)
            ->whereHas('categoria', function ($q) use ($miUnidadId) {
                $q->where('unidad_id', $miUnidadId);
            })
            ->count();

        //--tickets recientes por unidad del admin autenticado
        $todosLosTickets = Ticket::with(['user', 'categoria', 'estado'])
            ->whereHas('categoria', function ($q) use ($miUnidadId) {
                $q->where('unidad_id', $miUnidadId);
            })
            ->latest()
            ->get();

        //----Estadísticas mensuales filtradas por Unidad de Categoría----
        $añoActual = date('Y');
        $nombresMeses = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];
        $mesesGrafico = [];

        //---agrupa tickets por mes y estado
        $statsMensuales = Ticket::selectRaw('MONTH(created_at) as mes, estado_id, COUNT(*) as total')
            ->whereYear('created_at', $añoActual)
            ->whereHas('categoria', function ($q) use ($miUnidadId) {
                $q->where('unidad_id', $miUnidadId);
            })
            ->groupBy('mes', 'estado_id')
            ->get();

        for ($i = 1; $i <= 12; $i++) {
            //---tickets resueltos
            $res = $statsMensuales->where('mes', $i)->where('estado_id', 3)->sum('total');

            //--sumamos los pendientes   
            $pen = $statsMensuales->where('mes', $i)->where('estado_id', '!=', 3)->sum('total');

            $total = $res + $pen;

            $mesesGrafico[] = [
                'nombre' => $nombresMeses[$i - 1],
                'resueltos_pct' => $total > 0 ? round(($res / $total) * 100) : 0,
                'pendientes_pct' => $total > 0 ? round(($pen / $total) * 100) : 0,
                'total' => $total
            ];
        }


        //----manuales
        $manuales = Manual::latest()->take(3)->get();

        return view('admin_unidad.dashboard', compact('noAsignados', 'pendientes', 'resueltos', 'todosLosTickets', 'mesesGrafico', 'manuales'));
    }


     //-------------------------CLIENTE----------------------------
    public function create()
    {
        $categorias = Categoria::all();
        $tipos = TipoSolicitud::all();
        $prioridades = Prioridad::all();

        return view('admin_unidad.crear-ticket', compact('categorias', 'tipos', 'prioridades'));
    }

    public function store(Request $request)
    {
        //-----validacion datos
        $request->validate([
            'asunto' => 'required|string|min:5|max:50',
            'categoria_id' => 'required|exists:categorias,id',
            'tipo_solicitud_id' => 'required|exists:tipo_solicitudes,id',
            'descripcion' => 'required|string',
            'prioridad_id' => 'required|exists:prioridades,id',

        ], [
            'asunto.max' => 'El asunto es demasiado largo. Resume el problema en menos de 50 caracteres.',
            'asunto.min' => 'El asunto es demasiado corto. Debe tener al menos 5 caracteres.',
        ]);

        //--crear ticket
        $nuevoTicket = Ticket::create([
            'asunto' => $request->asunto,
            'descripcion' => $request->descripcion,
            'categoria_id' => $request->categoria_id,
            'tipo_solicitud_id' => $request->tipo_solicitud_id,
            'user_id' => Auth::id(), //----asignar el ticket al usuario autenticado
            'estado_id' => 1, //---abierto
            'prioridad_id' => $request->prioridad_id,
            'tecnico_id' => null, //---vacio inicial 
        ]);

        //---redireccionar con mensaje de exito
        return redirect()->route('admin_unidad.crear-ticket')
            ->with('success', '¡Ticket creado con éxito!');
    }



    //---metodos para las paginas---

    public function asignarTickets()
    {
        return "Página de Asignar Tickets (En construcción)";
    }

    public function misAsignados()
    {
        return "Página de Mis Asignados (En construcción)";
    }

    public function misTickets()
    {
        return "Historial de mis tickets (En construcción)";
    }

    public function recursos()
    {
        return "Biblioteca de manuales (En construcción)";
    }
}
