<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && !Auth::user()->activo) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Tu cuenta ha sido desactivada.'], 401);
            }

            return redirect()->route('login')->withErrors([
                'email' => 'Tu cuenta ha sido desactivada.',
            ]);
        }

        return $next($request);
    }
}
