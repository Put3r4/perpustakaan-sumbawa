<?php

namespace App\Http\Middleware;

use App\Models\Petugas;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePetugas
{
    /**
     * Ensure the authenticated user is a Petugas or SuperAdmin.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof Petugas || ! in_array($user->HakAkses, ['Petugas', 'SuperAdmin'], true)) {
            abort(403);
        }

        return $next($request);
    }
}
