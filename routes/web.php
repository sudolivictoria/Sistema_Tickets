<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\AdminUnidad\AdminUnidadController;
use App\Http\Controllers\ApiTableController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoriaManualController;
use App\Http\Controllers\Cliente\ClienteController;
use App\Http\Controllers\ManualController;
use App\Http\Controllers\TicketController;
use App\Models\Ticket;

Volt::route('/test-livewire', 'pages.test-livewire')->name('test.livewire');

//----------login 
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');



Route::middleware(['auth'])->group(function () {

    //---api para refresco automatico
    Route::get('/api/refresh-table', [ApiTableController::class, 'refresh'])->name('api.table.refresh');

    Route::get('/dashboard', function () {
        $user = Auth::user();
        if (!$user) return redirect('/');

        if ($user->rol_id == 1) { //----admin
            return redirect()->route('admin.dashboard');
        } elseif ($user->rol_id == 2) {
            return redirect()->route('usuario.dashboard');
        } elseif ($user->rol_id == 3) {
            return redirect()->route('gestor.dashboard');
        } else {
            return redirect('/'); //---si el rol no es admin ni cliente, redirige al login
        }
    })->name('dashboard');

    //---Rol Admin
    Route::middleware(['auth', 'role:Admin'])->prefix('admin')->name('admin.')->group(function () {

        //--dashboard principal
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

        //--seccion administracion
        Route::get('/asignar-tickets', [AdminController::class, 'asignarTickets'])->name('asignar-tickets');
        Route::get('/mis-asignados', [AdminController::class, 'misAsignados'])->name('mis-asignados');
        Route::get('/gestion-recursos', [AdminController::class, 'gestionRecursos'])->name('gestion-recursos');

        //---acciones para gestion de usuarios
        Route::post('/usuarios/store', [UserController::class, 'store'])->name('usuarios.store');
        Route::get('/gestion-usuarios', [UserController::class, 'index'])->name('gestion-usuarios');
        Route::patch('/usuarios/{user}', [UserController::class, 'update'])->name('usuarios.update');
        Route::patch('/usuarios/{user}/toggle', [UserController::class, 'toggleStatus'])->name('usuarios.toggle');

        //--seccion servicios-
        Route::get('/crear-ticket', [AdminController::class, 'create'])->name('crear-ticket');
        Route::get('/mis-tickets', [AdminController::class, 'misTickets'])->name('mis-tickets');
        Route::get('/recursos', [AdminController::class, 'recursos'])->name('recursos');
        Route::post('/crear-ticket', [AdminController::class, 'store'])->name('tickets.store');

        //---actualizar prioridad y tecnico
        Route::patch('/tickets/{ticket}/prioridad', [AdminController::class, 'actualizarPrioridad'])->name('actualizar-prioridad');
        Route::patch('/tickets/{ticket}/tecnico', [AdminController::class, 'actualizarTecnico'])->name('actualizar-tecnico');

        //---resolver ticket
        Route::patch('/tickets/{id}/resolver', [TicketController::class, 'resolver'])
            ->name('tickets.resolver');

        //---gestion de recursos
        Route::resource('manuales', ManualController::class);
        Route::post('/manuales', [ManualController::class, 'store'])->name('manuales.store');
        Route::put('/manuales/{id}', [ManualController::class, 'update'])->name('manuales.update');
        Route::delete('/manuales/{id}', [ManualController::class, 'destroy'])->name('manuales.destroy');

        //----gestion de categorias manuales
        Route::post('/categorias-manuales', [CategoriaManualController::class, 'store'])->name('categorias.store');
    });

    //---Rol Usuario
    Route::middleware(['role:Usuario'])->prefix('usuario')->name('usuario.')->group(function () {
        //-----dashboard principal
        Route::get('/dashboard', [ClienteController::class, 'index'])->name('dashboard');


        //---seccion operativa
        Route::get('/crear-ticket', [ClienteController::class, 'create'])->name('crear-ticket');
        Route::get('/mis-tickets', [ClienteController::class, 'misTickets'])->name('mis-tickets');
        Route::get('/recursos', [ClienteController::class, 'recursos'])->name('recursos');
        Route::post('/crear-ticket', [ClienteController::class, 'store'])->name('tickets.store');
    });


    //---Rol Gestor
    Route::middleware(['auth', 'role:Gestor'])->prefix('gestor')->name('gestor.')->group(function () {

        //--dashboard principal
        Route::get('/dashboard', [AdminUnidadController::class, 'index'])->name('dashboard');

        //--seccion administracion
        Route::get('/asignar-tickets', [AdminUnidadController::class, 'asignarTickets'])->name('asignar-tickets');
        Route::get('/mis-asignados', [AdminUnidadController::class, 'misAsignados'])->name('mis-asignados');


        //--seccion servicios-
        Route::get('/crear-ticket', [AdminUnidadController::class, 'create'])->name('crear-ticket');
        Route::get('/mis-tickets', [AdminUnidadController::class, 'misTickets'])->name('mis-tickets');
        Route::get('/recursos', [AdminUnidadController::class, 'recursos'])->name('recursos');
        Route::post('/crear-ticket', [AdminUnidadController::class, 'store'])->name('tickets.store');

        //---actualizar prioridad y tecnico
        Route::patch('/tickets/{ticket}/prioridad', [AdminUnidadController::class, 'actualizarPrioridad'])->name('actualizar-prioridad');
        Route::patch('/tickets/{ticket}/tecnico', [AdminUnidadController::class, 'actualizarTecnico'])->name('actualizar-tecnico');

        //---resolver ticket
        Route::patch('/tickets/{id}/resolver', [TicketController::class, 'resolver'])
            ->name('tickets.resolver');
    });
});

Route::get('/test-performance', function () {
    //--test
    return Ticket::with(['user', 'tecnico', 'prioridad', 'estado'])->limit(50)->get();
});

Route::post('/test-directo', function (Illuminate\Http\Request $request) {
    // 1. Creamos el ticket manualmente sin pasar por validaciones de Auth
    $ticket = Ticket::create([
        'asunto'            => $request->asunto ?? 'Ticket de Stress',
        'descripcion'       => $request->descripcion ?? 'Prueba de carga',
        'categoria_id'      => $request->categoria_id ?? 1,
        'tipo_solicitud_id' => $request->tipo_solicitud_id ?? 1,
        'user_id'           => 1, // ID de un usuario que ya exista
        'estado_id'         => 1,
        'prioridad_id'      => $request->prioridad_id ?? 1,
    ]);

    // 2. Intentamos enviar el correo AQUÍ para ver cuánto tarda
    // Si esta línea está activa, el test medirá el tiempo del correo
    \Illuminate\Support\Facades\Mail::to('test@example.com')
        ->send(new \App\Mail\NuevaSolicitudUnidadMail($ticket));

    return "Creado exitosamente ID: " . $ticket->id;
});
