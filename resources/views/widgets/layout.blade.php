<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $widgetName ?? 'Widget CCV' }}</title>
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/css/adminlte.min.css') }}">
    @stack('styles')
</head>
{{--
    pointer-events:none en body: el iframe del chatbot ocupa una esquina fija de 420×640 px
    en el lanzador para no bloquear la UI. Con pointer-events:none en el fondo, solo los
    elementos dentro de #ccv-widget-root (que lo sobreescribe a 'all') capturan eventos.
    Para tipos modal (survey, notification, announcement) el lanzador los muestra a pantalla
    completa, por lo que no se requiere este truco, pero tampoco hace daño.
--}}
<body style="margin:0; padding:0; background:transparent; overflow:hidden; pointer-events:none;">

<div id="ccv-widget-root" style="pointer-events:all;">
    @yield('widget-content')
</div>

<script>
    /**
     * Configuración del widget inyectada por el servidor.
     * Disponible globalmente como window.CCV
     */
    window.CCV = {
        widgetSlug:  '{{ $widgetSlug ?? '' }}',
        widgetType:  '{{ $widgetType ?? '' }}',
        launcherUrl: '{{ rtrim(config('sso.launcher_url'), '/') }}',
        userId:      '{{ auth()->id() ?? '' }}',
        userName:    '{{ auth()->user()?->name ?? '' }}',
    };

    /**
     * Envía un evento postMessage al lanzador (host del iframe).
     *
     * Tipos estándar:
     *   widget:ready      → el widget terminó de cargar
     *   widget:close      → el usuario pidió cerrar el widget
     *   widget:submitted  → el usuario completó una acción
     *   widget:resize     → el widget necesita cambiar de tamaño
     */
    window.cCVSend = function(type, data = {}) {
        if (!window.CCV.launcherUrl) return;
        window.parent.postMessage(
            { source: 'ccv-widget', type, widgetSlug: window.CCV.widgetSlug, data },
            window.CCV.launcherUrl
        );
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
