<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso no autorizado</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: system-ui, -apple-system, sans-serif;
            display: flex; align-items: center; justify-content: center;
            min-height: 100vh; background: #f8fafc; color: #64748b;
        }
        .msg { text-align: center; }
        .msg svg { display: block; margin: 0 auto 14px; }
        .msg p { font-size: .95rem; }
    </style>
</head>
<body>
    <div class="msg">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <p>No tienes permisos para acceder a <strong>{{ $app_name ?? 'esta aplicación' }}</strong>.</p>
        <p style="margin-top:6px;font-size:.85rem;">Cerrando pestaña…</p>
    </div>

    <script>
        (function () {
            var appName    = @json($app_name ?? null);
            var launcherUrl = @json(rtrim($launcher_url ?? config('sso.launcher_url', '/'), '/'));

            var notification = JSON.stringify({
                type: 'access_denied',
                app:  appName,
                message: 'No tienes permisos para acceder a ' + (appName ?? 'esta aplicación'),
                ts: Date.now()
            });

            // Enviar notificación por localStorage (cross-tab más fiable)
            try { localStorage.setItem('sso_notification', notification); } catch (e) {}

            // Intenta enfocar el lanzador y cerrar esta pestaña.
            // setTimeout es el fallback por si window.close() falla silenciosamente.
            function goToLauncher(launcherWin, extra) {
                if (extra) { try { extra(); } catch (e) {} }
                if (launcherWin) { try { launcherWin.focus(); } catch (e) {} }
                window.close();
                setTimeout(function () { window.location.href = launcherUrl + '?error=sso_unauthorized'; }, 500);
            }

            // Método 1: window.opener
            if (window.opener && !window.opener.closed) {
                try {
                    goToLauncher(window.opener, function () {
                        window.opener.postMessage({ type: 'sso_access_denied', app: appName }, '*');
                    });
                    return;
                } catch (e) { /* cross-origin — continuar */ }
            }

            // Método 2: pestaña con window.name = 'sso-launcher'
            var launcherWin = null;
            try { launcherWin = window.open('', 'sso-launcher'); } catch (e) {}

            if (launcherWin && launcherWin !== window && !launcherWin.closed) {
                var launcherOpen = false;
                try { launcherOpen = launcherWin.location.href !== 'about:blank'; }
                catch (e) { launcherOpen = true; }

                if (launcherOpen) {
                    goToLauncher(launcherWin, function () {
                        launcherWin.postMessage({ type: 'sso_access_denied', app: appName }, '*');
                    });
                    return;
                } else {
                    launcherWin.close();
                }
            }

            // Sin lanzador abierto: redirigir en esta misma pestaña
            window.location.href = launcherUrl + '?error=sso_unauthorized';
        })();
    </script>
</body>
</html>
