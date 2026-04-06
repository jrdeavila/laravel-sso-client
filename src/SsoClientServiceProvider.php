<?php

namespace CamaradeComercioDeValledupar\SsoClient;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use CamaradeComercioDeValledupar\SsoClient\Crypto\SsoSigner;
use CamaradeComercioDeValledupar\SsoClient\Http\Middleware\SsoAuthenticate;
use CamaradeComercioDeValledupar\SsoClient\Http\Middleware\ValidateSsoToken;
use CamaradeComercioDeValledupar\SsoClient\Services\SsoTokenService;

class SsoClientServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/sso.php', 'sso');

        $this->app->singleton(SsoSigner::class, fn () => new SsoSigner(
            config('sso.secret')
        ));

        $this->app->singleton(SsoTokenService::class, fn ($app) => new SsoTokenService(
            $app->make(SsoSigner::class)
        ));
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/sso.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'sso-client');

        $this->app['router']->aliasMiddleware('sso.token', ValidateSsoToken::class);
        $this->app['router']->aliasMiddleware('sso.auth', SsoAuthenticate::class);

        // Interceptar AuthenticationException (lanzada por el middleware 'auth' de Laravel)
        // para redirigir al launcher en lugar de a /login.
        $this->app->make(ExceptionHandler::class)
            ->renderable(function (AuthenticationException $e, Request $request) {
                if ($request->expectsJson()) {
                    return null; // dejar que el handler por defecto responda 401
                }

                return response()->view('sso-client::go-to-launcher', [
                    'launcher_url' => rtrim(config('sso.launcher_url'), '/'),
                ]);
            });

        $this->publishes([
            __DIR__ . '/../config/sso.php' => config_path('sso.php'),
        ], 'sso-config');
    }
}
