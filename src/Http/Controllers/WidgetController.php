<?php

namespace CamaradeComercioDeValledupar\SsoClient\Http\Controllers;

use Illuminate\Http\Request;
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

    /**
     * Check server-to-server para announcements con check_class configurado.
     *
     * El lanzador llama GET /widgets/{slug}/check?token=... antes de mostrar
     * el anuncio. Si retorna {"show": false}, el lanzador lo omite en esa visita.
     *
     * La check_class es una clase invocable: __invoke(Request $request): bool
     * El token SSO ya fue validado por el middleware sso.token antes de llegar aquí.
     */
    public function check(string $slug, Request $request)
    {
        $widget = WidgetRegistry::find($slug);

        abort_if(! $widget, 404, "Widget '{$slug}' no encontrado o inactivo.");

        $checkClass = $widget['check_class'] ?? null;

        if (! $checkClass || ! class_exists($checkClass)) {
            return response()->json(['show' => true]);
        }

        $show = (bool) app($checkClass)($request);

        return response()->json(['show' => $show]);
    }
}
