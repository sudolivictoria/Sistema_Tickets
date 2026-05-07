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
            return redirect('/login');
        }

        //----obtiene el usuario autenticado
        $user = Auth::user();

        $requiredRoleId = $roles[$role] ?? null;

        //----evitar error si el rol no existe en el diccionario
        if (intval($user->rol_id) === 1) {
            return $next($request);
        }

        if ($user->rol && $user->rol->nombre_rol === $role) {
            return $next($request);
        }

        abort(403, "Acceso denegado.");
    }
}
