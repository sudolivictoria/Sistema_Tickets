<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LoginController::class, 'showLoginForm']);

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');


Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

//-----------------Routes para el login-----------------
Route::middleware(['auth'])->group(function () {

    //-----------rutas de admin
    Route::middleware(['role:Admin'])->prefix('admin')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');
    });

    //-----------rutas de cliente
    Route::middleware(['role:Cliente'])->prefix('cliente')->group(function () {
        Route::get('/dashboard', function () {
            return view('cliente.dashboard');
        })->name('cliente.dashboard');
    });
});

require __DIR__ . '/auth.php';
