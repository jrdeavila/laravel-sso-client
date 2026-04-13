<?php

namespace CamaradeComercioDeValledupar\SsoClient;

use CamaradeComercioDeValledupar\SsoClient\Crypto\SsoSigner;
use CamaradeComercioDeValledupar\SsoClient\Http\Middleware\SsoAuthenticate;
use CamaradeComercioDeValledupar\SsoClient\Http\Middleware\ValidateSsoToken;
use CamaradeComercioDeValledupar\SsoClient\Http\Middleware\WidgetSessionMiddleware;
use CamaradeComercioDeValledupar\SsoClient\Services\SsoTokenService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class SsoClientServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/sso.php', 'sso');

        $this->app->singleton(SsoSigner::class, fn () => new SsoSigner(
            config('sso.secret')
        ));

        $this->app->singleton(SsoTokenService::class, fn ($app) => new SsoTokenService(
            $app->make(SsoSigner::class)
        ));

        $this->registerWidgetFeature();
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/sso.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'sso-client');
        View::addNamespace('ccv', __DIR__.'/../resources/views');

        $this->app['router']->aliasMiddleware('sso.token', ValidateSsoToken::class);
        $this->app['router']->aliasMiddleware('sso.auth', SsoAuthenticate::class);
        $this->app['router']->aliasMiddleware('sso.widget_session', WidgetSessionMiddleware::class);

        // Inyectar SsoAuthenticate en el grupo 'web' SOLO en apps receptoras.
        // Si is_launcher=true el paquete está instalado en el propio lanzador:
        // no se inyecta para evitar el bucle infinito de redirección.
        if (! config('sso.is_launcher', false)) {
            $this->app['router']->pushMiddlewareToGroup('web', SsoAuthenticate::class);
        }

        $this->publishes([
            __DIR__.'/../config/sso.php' => config_path('sso.php'),
        ], 'sso-config');

        $this->publishes([
            __DIR__.'/../config/widgets.php' => config_path('widgets.php'),
        ], 'ccv-widgets-config');

        $this->publishes([
            __DIR__.'/../resources/views/widgets' => resource_path('views/vendor/ccv/widgets'),
        ], 'ccv-widgets-views');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/ccv'),
        ], 'ccv-views');

        $this->bootWidgetFeature();
    }

    /**
     * Registra la config de widgets. mergeConfigFrom es seguro aunque
     * el archivo no exista en la app — config('widgets') quedará en null.
     */
    protected function registerWidgetFeature(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/widgets.php',
            'widgets'
        );
    }

    /**
     * Activa rutas y vistas de widgets SOLO si la app publicó widgets.php.
     * Si el archivo no existe, esta feature no se activa — opt-in limpio.
     * Reutiliza el namespace 'sso-client' ya registrado por el SSO para las vistas.
     */
    protected function bootWidgetFeature(): void
    {
        if (! file_exists(config_path('widgets.php'))) {
            return;
        }

        $this->loadRoutesFrom(__DIR__.'/../routes/widgets.php');

        // El namespace 'sso-client' apunta a resources/views/ del paquete.
        // Las vistas SSO están en sso-client::sso.X
        // Las vistas de widgets están en sso-client::widgets.X
        // Mismo namespace, subdirectorios distintos — sin conflicto.
    }
}
