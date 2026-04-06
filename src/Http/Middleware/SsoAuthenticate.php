<?php

namespace CamaradeComercioDeValledupar\SsoClient\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class SsoAuthenticate
{
    public function handle(Request $request, Closure $next): mixed
    {
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
