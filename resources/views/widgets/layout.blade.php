<!DOCTYPE html>
<html lang="es" style="height:100%;overflow:hidden;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $widgetName ?? 'Widget CCV' }}</title>
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/css/adminlte.min.css') }}">
    <style>
        html, body { height: 100%; overflow: hidden; }
        body { margin: 0; padding: 0; background: transparent; }
        #ccv-widget-root { width: 100%; height: 100%; overflow: hidden; }
    </style>
    @stack('styles')
</head>
<body>

<div id="ccv-widget-root">
    @yield('widget-content')
</div>

<script>
    /** Configuración del widget inyectada por el servidor. */
    window.CCV = {
        widgetSlug:  '{{ $widgetSlug ?? '' }}',
        widgetType:  '{{ $widgetType ?? '' }}',
        launcherUrl: '{{ rtrim(config('sso.launcher_url'), '/') }}',
        userId:      '{{ auth()->id() ?? '' }}',
        userName:    '{{ auth()->user()?->name ?? '' }}',
    };

    /**
     * Envía un evento postMessage al lanzador.
     *
     * Tipos estándar:
     *   widget:ready        → widget terminó de cargar
     *   widget:close        → usuario cerró el widget
     *   widget:submitted    → usuario completó la acción (survey → cierre permanente)
     *   notification:show   → mostrar toast  { title, message, type, duration }
     */
    window.cCVSend = function(type, data = {}) {
        if (!window.CCV.launcherUrl) return;
        window.parent.postMessage(
            { source: 'ccv-widget', type, widgetSlug: window.CCV.widgetSlug, data },
            window.CCV.launcherUrl
        );
    };

    /**
     * Shorthand para widgets tipo notification.
     * @param {string} title    Título (puede estar vacío)
     * @param {string} message  Cuerpo del mensaje
     * @param {string} type     info | success | warning | error
     * @param {number} duration ms antes de auto-cerrar (0 = solo manual)
     */
    window.cCVNotify = function(title, message, type = 'info', duration = 5000) {
        window.cCVSend('notification:show', { title, message, type, duration });
    };

    document.addEventListener('DOMContentLoaded', function () {
        window.cCVSend('widget:ready');
    });
</script>

<script src="{{ asset('vendor/adminlte/js/adminlte.min.js') }}"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@stack('scripts')

</body>
</html>
