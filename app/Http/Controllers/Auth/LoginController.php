<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    //-----------vista del login
    public function showLoginForm()
    {
        if (Auth::check()) {
            $user = Auth::user();

            if (!$user->rol) {
                $user = User::find($user->id);
            }

            $rol = $user->rol ? $user->rol->nombre_rol : '';
            return match ($rol) {
                'Admin'   => redirect()->route('admin.dashboard'),
                'Usuario' => redirect()->route('usuario.dashboard'),
                'Gestor'  => redirect()->route('gestor.dashboard'),
                default   => redirect('/'),
            };
        }
        return view('auth.login');
    }

    //---------proceso de login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {

            if (Auth::user()->activo == 0) {
                Auth::logout();
                return back()->withErrors(['email' => 'Tu cuenta está desactivada.']);
            }
            $request->session()->regenerate();
            $user = Auth::user();
            $rol = $user->rol->nombre_rol; //---->obtener el nombre del rol del usuario autenticado

            //-----------redireccionar segun el rol (se puede escalar)
            if ($rol === 'Admin') {
                return redirect()->intended('/admin/dashboard');
            } else if ($rol === 'Usuario') {
                return redirect()->intended('/usuario/dashboard');
            } else if ($rol === 'Gestor') {
                return redirect()->intended('/gestor/dashboard');
            }
            return redirect('/');
        }

        //--------si falla el login
        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    //---------cerrar sesion
    public function logout(Request $request)
    {
        $user = User::find(Auth::id());

        if ($user) {
            $user->remember_token = null;
            $user->save();
        }

        Auth::logout(); 
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
