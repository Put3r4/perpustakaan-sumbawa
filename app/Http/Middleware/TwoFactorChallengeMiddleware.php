<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorChallengeMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Fortify stores 'login.id' in the session during the authentication pipeline
        // if the user has two-factor authentication enabled.
        if (! $request->session()->has('login.id')) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
