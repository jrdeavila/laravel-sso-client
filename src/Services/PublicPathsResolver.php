<?php

namespace CamaradeComercioDeValledupar\SsoClient\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PublicPathsResolver
{
    /**
     * Rutas internas del paquete que nunca requieren autenticación.
     * Los endpoints de widgets y SSO usan sso.token como seguridad propia.
     */
    private array $builtIn = ['sso/*', 'widgets/*'];

    /**
     * Devuelve todos los patrones de ruta pública:
     * rutas built-in del paquete + rutas del config local + rutas del lanzador (cacheadas).
     */
    public function resolve(): array
    {
        return array_values(array_unique(array_merge(
            $this->builtIn,
            config('sso.public_paths', []),
            $this->fetchFromLauncher(),
        )));
    }

    private function fetchFromLauncher(): array
    {
        $launcherUrl = config('sso.launcher_url');
        $secret      = config('sso.secret');
        $ttl         = (int) config('sso.public_paths_cache_ttl', 300);

        if (! $launcherUrl || ! $secret) {
            return [];
        }

        if ($ttl === 0) {
            return $this->callLauncher($launcherUrl, $secret);
        }

        $cacheKey = 'sso:public_paths:' . hash('sha256', $secret);

        return Cache::remember($cacheKey, $ttl, fn () => $this->callLauncher($launcherUrl, $secret));
    }

    private function callLauncher(string $launcherUrl, string $secret): array
    {
        try {
            $response = Http::timeout(5)->get(
                rtrim($launcherUrl, '/') . '/sso/public-paths',
                ['secret' => $secret]
            );

            if ($response->ok()) {
                $paths = $response->json('paths', []);
                return is_array($paths) ? array_values(array_filter($paths, 'is_string')) : [];
            }
        } catch (\Throwable) {
            // Si el lanzador no está disponible, seguir sin las rutas remotas.
        }

        return [];
    }
}
