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
