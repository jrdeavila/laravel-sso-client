<?php

namespace CCV\SsoClient;

use Illuminate\Support\ServiceProvider;
use CCV\SsoClient\Crypto\SsoSigner;
use CCV\SsoClient\Http\Middleware\ValidateSsoToken;
use CCV\SsoClient\Services\SsoTokenService;

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

        $this->app['router']->aliasMiddleware('sso.token', ValidateSsoToken::class);

        $this->publishes([
            __DIR__ . '/../config/sso.php' => config_path('sso.php'),
        ], 'sso-config');
    }
}
