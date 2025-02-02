<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientiMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && (auth()->user()->ruolo->nome === 'Amministratore' || auth()->user()->ruolo->nome === 'Dipendente')) {
            return $next($request);
        }

        return redirect('/')->with('error', 'Non hai i permessi necessari per accedere a questa pagina.');
    }
}
