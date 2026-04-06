<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cerrando sesión…</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: system-ui, -apple-system, sans-serif;
            display: flex; align-items: center; justify-content: center;
            min-height: 100vh; background: #f8fafc; color: #64748b;
        }
        .msg { text-align: center; }
        .msg svg { display: block; margin: 0 auto 14px; animation: spin 1s linear infinite; }
        .msg p { font-size: .95rem; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div class="msg">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.5"
             stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
        </svg>
        <p>Cerrando sesión…</p>
    </div>

    <script>
        (function () {
            var launcherUrl = @json($launcher_url);

            // Método 1: esta pestaña fue abierta directamente desde el launcher (window.opener).
            if (window.opener && !window.opener.closed) {
                try {
                    window.opener.focus();
                    window.close();
                    return;
                } catch (e) {
                    // Error cross-origin — continuar con método 2
                }
            }

            // Método 2: buscar cualquier pestaña del launcher por window.name = 'sso-launcher'.
            // window.open('', nombre) devuelve la pestaña existente con ese nombre sin abrir una nueva;
            // si no existe ninguna, abre una en blanco (que cerramos de inmediato).
            var launcherWin = null;
            try {
                launcherWin = window.open('', 'sso-launcher');
            } catch (e) { /* bloqueado por el navegador */ }

            if (launcherWin && launcherWin !== window && !launcherWin.closed) {
                var launcherIsOpen = false;
                try {
                    // Mismo origen: podemos leer href directamente
                    launcherIsOpen = launcherWin.location.href !== 'about:blank';
                } catch (crossOriginError) {
                    // Error cross-origin significa que es una página real → el launcher está abierto
                    launcherIsOpen = true;
                }

                if (launcherIsOpen) {
                    launcherWin.focus();
                    window.close();
                    return;
                } else {
                    // Era una ventana en blanco generada por window.open — cerrarla
                    launcherWin.close();
                }
            }

            // Sin launcher abierto: redirigir en esta misma pestaña.
            window.location.href = launcherUrl;
        })();
    </script>
</body>
</html>
