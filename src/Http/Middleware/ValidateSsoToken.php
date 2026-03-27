<?php

namespace CamaradeComercioDeValledupar\SsoClient\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use CamaradeComercioDeValledupar\SsoClient\Services\SsoTokenService;

class ValidateSsoToken
{
    public function __construct(private readonly SsoTokenService $ssoToken) {}

    public function handle(Request $request, Closure $next): mixed
    {
        $token = $request->query(config('sso.token_param'));

        if (! $token || ! $this->ssoToken->isValid($token)) {
            return redirect(config('sso.launcher_url') . '?error=sso_invalid');
        }

        return $next($request);
    }
}
