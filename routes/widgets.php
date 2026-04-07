<?php

use Illuminate\Support\Facades\Route;
use CamaradeComercioDeValledupar\SsoClient\Http\Controllers\WidgetController;

/*
|--------------------------------------------------------------------------
| Rutas de Widget Provider
|--------------------------------------------------------------------------
|
| Protegidas por el middleware sso.token (ValidateSsoToken) — el mismo de /sso/callback.
|
| GET /widgets/manifest?token=  → lista widgets disponibles (para el lanzador)
| GET /widgets/{slug}?token=    → renderiza el widget en iframe
|
| NOTA: 'manifest' debe ir ANTES de '{slug}' para que el segmento dinámico
| no capture la palabra "manifest" como slug.
*/

Route::middleware(['web', 'sso.token'])
    ->prefix('widgets')
    ->name('ccv.widgets.')
    ->group(function () {

        Route::get('/manifest', [WidgetController::class, 'manifest'])
            ->name('manifest');

        Route::get('/{slug}', [WidgetController::class, 'show'])
            ->name('show');

    });
