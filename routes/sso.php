<?php

use Illuminate\Support\Facades\Route;
use CCV\SsoClient\Http\Controllers\SsoController;

Route::middleware(['web', 'sso.token'])
    ->prefix('sso')
    ->group(function () {
        Route::get('/callback', [SsoController::class, 'handleCallback'])
             ->name('sso.callback');
    });
