<?php

use Illuminate\Support\Facades\Route;
use CamaradeComercioDeValledupar\SsoClient\Http\Controllers\SsoController;

// Callback SSO — requiere token válido en query param
Route::middleware(['web', 'sso.token'])
    ->prefix('sso')
    ->group(function () {
        Route::get('/callback', [SsoController::class, 'handleCallback'])
             ->name('sso.callback');
    });

// Logout SSO — invalida sesión y gestiona cierre de pestaña
Route::middleware(['web'])
    ->prefix('sso')
    ->group(function () {
        Route::post('/logout', [SsoController::class, 'logout'])
             ->name('sso.logout');
    });

// Verificación de secret — llamada server-to-server desde el lanzador (sin CSRF)
Route::post('/sso/verify-secret', function (\Illuminate\Http\Request $request) {
    $secret = $request->input('secret', '');
    $ok = $secret !== '' && hash_equals(config('sso.secret', ''), $secret);
    return response()->json(['ok' => $ok]);
})->name('sso.verify-secret');

// Rutas públicas locales — el lanzador las consulta para mostrarlas en el panel admin.
// Autenticado con service token (sub=0) firmado con el secret de esta app.
Route::get('/sso/local-public-paths', function (\Illuminate\Http\Request $request) {
    $token = $request->input('token', '');

    if ($token === '') {
        return response()->json(['paths' => []], 401);
    }

    try {
        $payload = app(\CamaradeComercioDeValledupar\SsoClient\Crypto\SsoSigner::class)->decode($token);
    } catch (\Throwable) {
        return response()->json(['paths' => []], 401);
    }

    if (($payload['sub'] ?? -1) !== 0) {
        return response()->json(['paths' => []], 403);
    }

    return response()->json(['paths' => config('sso.public_paths', [])]);
})->name('sso.local-public-paths');
