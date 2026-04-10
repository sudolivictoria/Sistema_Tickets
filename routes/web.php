<?php

use App\Http\Controllers\Admin\AdminController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Cliente\ClienteController;

Volt::route('/test-livewire', 'pages.test-livewire')->name('test.livewire');

//----------login 
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');



Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        $user = Auth::user();
        if (!$user) return redirect('/');

        if ($user->rol_id == 1) { //----admin
            return redirect()->route('admin.dashboard');
        } elseif ($user->rol_id == 2) {
            return redirect()->route('cliente.dashboard');
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
        Route::get('/gestion-usuarios', [AdminController::class, 'gestionUsuarios'])->name('gestion-usuarios');
        Route::get('/gestion-recursos', [AdminController::class, 'gestionRecursos'])->name('gestion-recursos');

        //--seccion servicios-
        Route::get('/crear-ticket', [AdminController::class, 'create'])->name('crear-ticket');
        Route::get('/mis-tickets', [AdminController::class, 'misTickets'])->name('mis-tickets');
        Route::get('/recursos', [AdminController::class, 'recursos'])->name('recursos');
    });

    //---Rol Cliente
    Route::middleware(['role:Cliente'])->prefix('cliente')->name('cliente.')->group(function () {
        //-----dashboard principal
        Route::get('/dashboard', [ClienteController::class, 'index'])->name('dashboard');


        //---seccion operativa
        Route::get('/crear-ticket', [ClienteController::class, 'create'])->name('crear-ticket');
        Route::get('/mis-tickets', [ClienteController::class, 'misTickets'])->name('mis-tickets');
        Route::get('/recursos', [ClienteController::class, 'recursos'])->name('recursos');
        Route::post('/crear-ticket', [ClienteController::class, 'store'])->name('tickets.store');

    });
});

// Comenta o borra esta línea si te da error
// require __DIR__ . '/auth.php';