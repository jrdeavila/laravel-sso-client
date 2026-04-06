<?php

namespace CamaradeComercioDeValledupar\SsoClient\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SsoAuthenticate
{
    // Rutas accesibles sin autenticación (rutas propias del paquete SSO)
    private array $except = ['sso/*'];

    public function handle(Request $request, Closure $next): mixed
    {
        // Dejar pasar rutas excluidas
        foreach ($this->except as $pattern) {
            if ($request->is($pattern)) {
                return $next($request);
            }
        }

        if (Auth::check()) {
            return $next($request);
        }

        // Petición AJAX / API: responder 401 sin HTML
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->view('sso-client::go-to-launcher', [
            'launcher_url' => rtrim(config('sso.launcher_url'), '/'),
        ]);
    }
}
