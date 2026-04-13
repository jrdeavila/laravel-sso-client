<?php

use CamaradeComercioDeValledupar\SsoClient\Http\Controllers\WidgetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas de Widget Provider
|--------------------------------------------------------------------------
|
| Protegidas por el middleware sso.token (ValidateSsoToken) — el mismo de /sso/callback.
|
| GET /widgets/manifest?token=       → lista widgets disponibles (para el lanzador)
| GET /widgets/{slug}/check?token=   → check server-to-server para announcements
| GET /widgets/{slug}?token=         → renderiza el widget en iframe
|
| NOTA: las rutas estáticas ('manifest') y las sub-rutas ('/{slug}/check') deben ir
| ANTES del segmento dinámico '/{slug}' para evitar capturas incorrectas.
*/

Route::middleware(['web', 'sso.token', 'sso.widget_session'])
    ->prefix('widgets')
    ->name('ccv.widgets.')
    ->group(function () {

        Route::get('/manifest', [WidgetController::class, 'manifest'])
            ->name('manifest');

        Route::get('/{slug}/check', [WidgetController::class, 'check'])
            ->name('check');

        Route::get('/{slug}', [WidgetController::class, 'show'])
            ->name('show');

    });
