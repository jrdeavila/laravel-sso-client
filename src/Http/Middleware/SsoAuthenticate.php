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
                return $next($request);
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
