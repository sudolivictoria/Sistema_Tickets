<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!auth()->check()) {
            return redirect('/');
        }

        $user = auth()->user();

        $roles = [
            'Admin'   => 1,
            'Cliente' => 2,
        ];

        $requiredRoleId = $roles[$role] ?? null;
        
        if ($user->rol_id != $requiredRoleId) {
            abort(403, "Acceso denegado. Tu rol ID es {$user->rol_id} y se requiere ID {$requiredRoleId}");
        }

        return $next($request);
    }
}