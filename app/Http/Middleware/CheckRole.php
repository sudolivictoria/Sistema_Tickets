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

        //----diccionario de roles 
        $roles = [
            'Admin'   => 1,
            'Usuario' => 2,
            'Gestor' => 3,
            '1'       => 1,
            '2'       => 2,
            '3'       => 3,

        ];

        $requiredRoleId = $roles[$role] ?? null;

        //----evitar error si el rol no existe en el diccionario
       if (intval($user->rol_id) === 1) {
            return $next($request);
        }

       if (intval($user->rol_id) === intval($requiredRoleId)) {
            return $next($request);
        }

        abort(403, "Acceso denegado.");
    }
}