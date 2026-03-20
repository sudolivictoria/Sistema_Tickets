<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    //-----------vista del login
    public function showLoginForm()
    {
        return view('auth.login');
    }

    //---------proceso de login
    public function login(Request $request)
    {
        //------validar credenciales del formulario
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        //----intentar inicio de sesion
        if (Auth::attempt($credentials)) {

            //-----verificar si el usuario esta activo
            if (!Auth::user()->activo) {
                Auth::logout();
                return back()->withErrors(['email' => 'Tu cuenta está desactivada.']);
            }

            $request->session()->regenerate();
            $user = Auth::user();
            $rol = $user->rol->nombre_rol; //---->obtener el nombre del rol del usuario autenticado

            //-----------redireccionar segun el rol (se puede escalar)
            if ($rol === 'Admin') {
                return redirect()->intended('/admin/dashboard');
            } else if ($rol === 'Cliente') {
                return redirect()->intended('/cliente/dashboard');
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
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
