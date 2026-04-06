<?php

namespace CamaradeComercioDeValledupar\SsoClient;

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

        // Inyectar SsoAuthenticate en el grupo 'web' para que todas las rutas
        // de la receptora redirijan al launcher cuando no hay sesión activa.
        $this->app['router']->pushMiddlewareToGroup('web', SsoAuthenticate::class);

        $this->publishes([
            __DIR__ . '/../config/sso.php' => config_path('sso.php'),
        ], 'sso-config');
    }
}
