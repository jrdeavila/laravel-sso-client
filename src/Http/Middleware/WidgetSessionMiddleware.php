<?php

namespace CamaradeComercioDeValledupar\SsoClient\Http\Middleware;

use CamaradeComercioDeValledupar\SsoClient\Services\SsoTokenService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class WidgetSessionMiddleware
{
    public function __construct(private readonly SsoTokenService $ssoToken) {}

    public function handle(Request $request, Closure $next): mixed
    {
        // Usa el mismo parámetro que ValidateSsoToken — por defecto 'token'.
        // El nombre configurable evita que ambos middlewares lean params distintos.
        $token = $request->query(config('sso.token_param', 'token'));

        if ($token && $this->ssoToken->isValid($token)) {
            $payload = $this->ssoToken->decode($token);
            $userId = $payload->sub ?? null;
            $appSlug = $payload->app ?? null;

            if ($userId && $appSlug) {
                Session::put('sso_widget_user_id', $userId);
                Session::put('sso_widget_app', $appSlug);
            }
        }

        return $next($request);
    }
}
