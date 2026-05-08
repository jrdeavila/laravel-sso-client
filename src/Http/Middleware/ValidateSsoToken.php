<?php

namespace CamaradeComercioDeValledupar\SsoClient\Http\Middleware;

use CamaradeComercioDeValledupar\SsoClient\Crypto\SsoSigner;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ValidateSsoToken
{
    public function __construct(private readonly SsoSigner $signer) {}

    public function handle(Request $request, Closure $next): mixed
    {
        $token = $request->query(config('sso.token_param'));

        if (! $token) {
            Log::warning('[SSO] Token ausente en la solicitud.', [
                'url' => $request->fullUrl(),
                'ip'  => $request->ip(),
            ]);
            return redirect(config('sso.launcher_url') . '?error=sso_invalid');
        }

        try {
            $this->signer->decode($token);
        } catch (\Throwable $e) {
            Log::error('[SSO] Token rechazado: ' . $e->getMessage(), [
                'reason'       => $e->getMessage(),
                'token_prefix' => substr($token, 0, 40) . '…',
                'url'          => $request->fullUrl(),
                'ip'           => $request->ip(),
                'user_agent'   => $request->userAgent(),
            ]);
            return redirect(config('sso.launcher_url') . '?error=sso_invalid');
        }

        return $next($request);
    }
}
