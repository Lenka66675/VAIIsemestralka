<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Skontroluje, či je používateľ prihlásený a má rolu admin
        if (Auth::check() && Auth::user()->isAdmin()) {
            return $next($request);
        }

        // Ak nie je admin, presmeruje na hlavnú stránku
        return redirect('/');
    }
}


