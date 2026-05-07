<?php

namespace CamaradeComercioDeValledupar\SsoClient\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApplySsoAppConfig
{
    public function handle(Request $request, Closure $next): mixed
    {
        $app = session(config('sso.app_meta_session_key', 'sso_app'));

        if (! $app || ! is_array($app)) {
            return $next($request);
        }

        config(['adminlte.title'       => $app['name'] ?? config('adminlte.title')]);
        config(['adminlte.logo_img_alt' => $app['name'] ?? '']);

        if (! empty($app['logo'])) {
            config(['adminlte.logo_img'    => $app['logo']]);
            config(['adminlte.logo_img_xl' => $app['logo']]);
            config(['adminlte.logo'        => '']);
        } elseif (! empty($app['icon'])) {
            config(['adminlte.logo'        => '<i class="' . e($app['icon']) . '"></i> ' . e($app['name'] ?? '')]);
            config(['adminlte.logo_img'    => '']);
            config(['adminlte.logo_img_xl' => '']);
        }

        return $next($request);
    }
}
