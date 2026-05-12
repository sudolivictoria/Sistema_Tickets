<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Maneja una solicitud entrante.
     */

    public function handle(Request $request, Closure $next, string $role): Response
    {
        //---verifica si el usuario esta logueado
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        //----obtiene el usuario autenticado
        $user = Auth::user();

        $roles = [
            'Admin'   => 1,
            'Usuario' => 2,
            'Gestor'  => 3,
        ];

        //----evitar error si el rol no existe en el diccionario
        if (intval($user->rol_id) === 1) {
            return $next($request);
        }

        if ($user->rol && $user->rol->nombre_rol === $role) {
            return $next($request);
        }

        $requiredRoleId = $roles[$role] ?? null;
        if ($requiredRoleId && intval($user->rol_id) === $requiredRoleId) {
            return $next($request);
        }

        Auth::logout();
        return redirect()->route('login')->with('error', 'Acceso no autorizado, intentalo de nuevo.');
    }
}
