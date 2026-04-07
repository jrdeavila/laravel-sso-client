<?php

namespace CamaradeComercioDeValledupar\SsoClient\Http\Controllers;

use Illuminate\Routing\Controller;
use CamaradeComercioDeValledupar\SsoClient\Widgets\WidgetRegistry;

class WidgetController extends Controller
{
    /**
     * Renderiza el widget como vista autónoma para iframe.
     * ValidateSsoToken middleware ya validó el token antes de llegar aquí.
     */
    public function show(string $slug)
    {
        $widget = WidgetRegistry::find($slug);

        abort_if(!$widget, 404, "Widget '{$slug}' no encontrado o inactivo.");

        return view($widget['view'], [
            'widgetSlug'   => $slug,
            'widgetName'   => $widget['name'],
            'widgetType'   => $widget['type'],
            'widgetLayout' => $widget['layout'] ?? 'sso-client::widgets.layout',
        ]);
    }

    /**
     * Manifiesto de widgets disponibles en esta app.
     * El lanzador consulta este endpoint al registrar la app como widget provider.
     */
    public function manifest()
    {
        return response()->json([
            'app_name' => config('app.name'),
            'app_url'  => config('app.url'),
            'widgets'  => WidgetRegistry::manifest(),
        ]);
    }
}
