<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePactSigned
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isAnggota() && !$user->hasSignedPact()) {
            if (!$request->routeIs('integrity-pact.*') && !$request->routeIs('logout')) {
                return redirect()->route('integrity-pact.show')->with('info', 'Silakan setujui dan tanda tangani Pakta Integritas Petugas Pendataan terlebih dahulu.');
            }
        }

        return $next($request);
    }
}
