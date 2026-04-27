<?php

namespace CamaradeComercioDeValledupar\SsoClient\Http\Middleware;

use CamaradeComercioDeValledupar\SsoClient\Services\PublicPathsResolver;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SsoAuthenticate
{
    public function __construct(private PublicPathsResolver $resolver) {}

    public function handle(Request $request, Closure $next): mixed
    {
        foreach ($this->resolver->resolve() as $pattern) {
            if ($request->is($pattern)) {
                $request->attributes->set('sso_public_path', true);

                try {
                    return $next($request);
                } catch (\Illuminate\Auth\AuthenticationException) {
                    // La ruta tiene middleware auth pero está declarada como public_path.
                    // Se devuelve 401 en vez de redirigir al lanzador.
                    // Solución: quitar auth de las rutas declaradas en sso.public_paths.
                    if ($request->expectsJson()) {
                        return response()->json(['message' => 'Unauthenticated.'], 401);
                    }

                    return response()->view('sso-client::public-path-auth-conflict', [], 401);
                }
            }
        }

        if (Auth::check()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->view('sso-client::go-to-launcher', [
            'launcher_url' => rtrim(config('sso.launcher_url'), '/'),
        ]);
    }
}
