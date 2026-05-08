<?php

namespace CamaradeComercioDeValledupar\SsoClient\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use CamaradeComercioDeValledupar\SsoClient\Services\SsoTokenService;

class SsoController extends Controller
{
    public function __construct(private readonly SsoTokenService $ssoToken) {}

    public function handleCallback(Request $request): RedirectResponse
    {
        $rawToken = $request->query(config('sso.token_param'));

        // 1. Validar y decodificar el token
        try {
            $payload = $this->ssoToken->decode($rawToken);
        } catch (\Throwable $e) {
            Log::error('[SSO] Fallo al decodificar token en callback: ' . $e->getMessage(), [
                'reason'       => $e->getMessage(),
                'token_prefix' => $rawToken ? substr($rawToken, 0, 40) . '…' : null,
                'ip'           => $request->ip(),
                'user_agent'   => $request->userAgent(),
            ]);
            return redirect(config('sso.launcher_url') . '?error=sso_failed');
        }

        // 2. Buscar el usuario en la DB compartida por su ID
        $userId = $payload->{config('sso.user_id_field')};
        $model  = config('sso.user_model');

        $user = app($model)::find($userId);

        if (! $user) {
            Log::error('[SSO] Usuario no encontrado en el callback SSO.', [
                'user_id'    => $userId,
                'user_model' => $model,
                'app_slug'   => $payload->app ?? null,
                'ip'         => $request->ip(),
            ]);
            return redirect(config('sso.launcher_url') . '?error=sso_user_not_found');
        }

        // 3. Persistir metadatos del app en sesión para que las vistas puedan
        //    mostrar logo, nombre y colores configurados en el lanzador.
        session([config('sso.app_meta_session_key', 'sso_app') => [
            'name'  => $payload->name  ?? null,
            'logo'  => $payload->logo  ?? null,
            'color' => $payload->color ?? null,
            'icon'  => $payload->icon  ?? null,
            'slug'  => $payload->app   ?? null,
        ]]);

        // 4. Login y redirect
        try {
            Auth::login($user);
        } catch (\Throwable $e) {
            Log::error('[SSO] Error al hacer Auth::login(): ' . $e->getMessage(), [
                'user_id' => $userId,
                'ip'      => $request->ip(),
            ]);
            return redirect(config('sso.launcher_url') . '?error=sso_failed');
        }

        Log::debug('[SSO] Login SSO exitoso.', [
            'user_id'  => $userId,
            'app_slug' => $payload->app ?? null,
            'ip'       => $request->ip(),
        ]);

        return redirect(config('sso.redirect_after_login'));
    }

    public function logout(Request $request): Response
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->view('sso-client::logout', [
            'launcher_url' => rtrim(config('sso.launcher_url'), '/'),
        ]);
    }
}
