<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;

// TEST: Verificar Livewire (solo para debug, no es necesario)
Volt::route('/test-livewire', 'pages.test-livewire')->name('test.livewire');

// 1. Login clásico sin Livewire (solución alternativa estable)
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');


// 2. Rutas Protegidas
Route::middleware(['auth'])->group(function () {

    // El "Semáforo" de redirección
    Route::get('/dashboard', function () {
        $user = Auth::user();
        if (!$user) return redirect('/');

        if ($user->rol_id == 1) { // 1 = Admin
            return redirect()->to('/admin/dashboard');
        }
        return redirect()->to('/cliente/dashboard');
    })->name('dashboard');

    // Rutas por Rol
    Route::middleware(['role:Admin'])->prefix('admin')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');
    });

    Route::middleware(['role:Cliente'])->prefix('cliente')->group(function () {
        Route::get('/dashboard', function () {
            return view('cliente.dashboard');
        })->name('cliente.dashboard');
    });
});

// Comenta o borra esta línea si te da error
// require __DIR__ . '/auth.php';