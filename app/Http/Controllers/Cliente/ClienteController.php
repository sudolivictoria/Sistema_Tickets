<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Manual;
use App\Models\Prioridad;
use App\Models\Ticket;
use App\Models\TipoSolicitud;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClienteController extends Controller
{
    public function index()
    {


        $userId = Auth::id();

        //----estadisticas tickets del cliente autenticado
        $abiertos = Ticket::where('user_id', $userId)
            ->whereNull('tecnico_id')
            ->where('estado_id', '!=', 3)
            ->count();

        $enProceso = Ticket::where('user_id', $userId)
            ->whereNotNull('tecnico_id')
            ->where('estado_id', '!=', 3)
            ->count();

        $resueltos = Ticket::where('user_id', $userId)
            ->where('estado_id', 3)
            ->count();


        $manuales = Manual::latest()->take(3)->get();

        //----tickets del cliente autenticado
        $todosLosTickets = Ticket::where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('cliente.dashboard', compact('abiertos', 'enProceso', 'resueltos', 'manuales', 'todosLosTickets'));
    }


    public function create()
    {
        $categorias = Categoria::all();
        $tipos = TipoSolicitud::all();
        $prioridades = Prioridad::all();

        return view('cliente.crear-ticket', compact('categorias', 'tipos', 'prioridades'));
    }



    public function store(Request $request)
    {
        //-----validacion datos
        $request->validate([
            'asunto' => 'required|max:255',
            'categoria_id' => 'required|exists:categorias,id',
            'tipo_solicitud_id' => 'required|exists:tipo_solicitudes,id',
            'descripcion' => 'required|string',
            'prioridad_id' => 'required|exists:prioridades,id',

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
        return redirect()->route('cliente.crear-ticket')
            ->with('success', '¡Ticket creado con éxito!');
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
