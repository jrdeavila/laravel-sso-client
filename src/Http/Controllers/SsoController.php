<?php

namespace CamaradeComercioDeValledupar\SsoClient\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use CamaradeComercioDeValledupar\SsoClient\Services\SsoTokenService;

class SsoController extends Controller
{
    public function __construct(private readonly SsoTokenService $ssoToken) {}

    public function handleCallback(Request $request): RedirectResponse
    {
        // 1. Validar y decodificar el token
        try {
            $payload = $this->ssoToken->decode(
                $request->query(config('sso.token_param'))
            );
        } catch (\Throwable) {
            return redirect(config('sso.launcher_url') . '?error=sso_failed');
        }

        // 2. Buscar el usuario en la DB compartida por su ID
        $userId = $payload->{config('sso.user_id_field')};
        $model  = config('sso.user_model');

        $user = app($model)::find($userId);

        if (! $user) {
            return redirect(config('sso.launcher_url') . '?error=sso_user_not_found');
        }

        // 3. Login y redirect
        Auth::login($user);

        return redirect(config('sso.redirect_after_login'));
    }
}
