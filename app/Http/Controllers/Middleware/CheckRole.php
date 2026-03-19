<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
   public function handle(Request $request, Closure $next, string $roleName): Response
{
    //---verificar si el usuario está autenticado---
    if (!Auth::check()) {
        return redirect('/login');
    }

    if (Auth::user()->rol->nombre !== $roleName) {
        abort(403, 'No tienes permiso para acceder a esta sección.');
    }

    return $next($request);
}
}
