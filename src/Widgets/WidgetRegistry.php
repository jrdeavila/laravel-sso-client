<?php

namespace CamaradeComercioDeValledupar\SsoClient\Widgets;

use InvalidArgumentException;

class WidgetRegistry
{
    protected static array $requiredKeys = ['name', 'type', 'view', 'enabled'];

    /**
     * Todos los widgets habilitados (enabled: true).
     */
    public static function all(): array
    {
        return collect(config('widgets.widgets', []))
            ->filter(fn($widget) => $widget['enabled'] ?? false)
            ->toArray();
    }

    /**
     * Busca un widget por slug. Retorna null si no existe o está desactivado.
     */
    public static function find(string $slug): ?array
    {
        $widget = config("widgets.widgets.{$slug}");

        if (!$widget || !($widget['enabled'] ?? false)) {
            return null;
        }

        return array_merge($widget, ['slug' => $slug]);
    }

    /**
     * Manifiesto público para el lanzador.
     * Expone slug, name, type y has_check.
     * Nunca rutas internas ni config sensible (check_class no se expone).
     */
    public static function manifest(): array
    {
        return collect(static::all())
            ->map(fn($widget, $slug) => [
                'slug'      => $slug,
                'name'      => $widget['name'],
                'type'      => $widget['type'],
                'has_check' => ! empty($widget['check_class']),
            ])
            ->values()
            ->toArray();
    }

    /**
     * Valida que un widget tenga todas las claves requeridas.
     * Lanza excepción en desarrollo para detectar configs incompletas.
     */
    public static function validate(string $slug, array $widget): void
    {
        foreach (static::$requiredKeys as $key) {
            if (!array_key_exists($key, $widget)) {
                throw new InvalidArgumentException(
                    "Widget '{$slug}' en config/widgets.php le falta la clave: '{$key}'"
                );
            }
        }
    }
}
