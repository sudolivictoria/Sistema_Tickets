<?php

use App\Http\Controllers\Admin\AdminController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;


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
        }
        return redirect()->route('cliente.dashboard');
    })->name('dashboard');

    //---Rol Admin
    Route::middleware(['role:Admin'])->prefix('admin')->group(function () {
        //-----controller
        Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    });

    //---Rol Cliente
    Route::middleware(['role:Cliente'])->prefix('cliente')->group(function () {
        Route::get('/dashboard', function () {
            return view('cliente.dashboard');
        })->name('cliente.dashboard');
    });
});

// Comenta o borra esta línea si te da error
// require __DIR__ . '/auth.php';