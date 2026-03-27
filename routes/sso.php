<?php

use Illuminate\Support\Facades\Route;
use CamaradeComercioDeValledupar\SsoClient\Http\Controllers\SsoController;

Route::middleware(['web', 'sso.token'])
    ->prefix('sso')
    ->group(function () {
        Route::get('/callback', [SsoController::class, 'handleCallback'])
             ->name('sso.callback');
    });

Route::prefix('sso')->group(function () {
    Route::post('/verify-secret', function (\Illuminate\Http\Request $request) {
        $secret = $request->input('secret', '');
        $ok = $secret !== '' && hash_equals(config('sso.secret', ''), $secret);
        return response()->json(['ok' => $ok]);
    })->name('sso.verify-secret');
});
