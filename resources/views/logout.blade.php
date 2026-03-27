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

            // Si la pestaña fue abierta desde el lanzador (window.opener existe y no está cerrado),
            // cerrar esta pestaña y enfocar el lanzador.
            if (window.opener && !window.opener.closed) {
                try {
                    window.opener.location.href = launcherUrl;
                    window.opener.focus();
                    window.close();
                    return;
                } catch (e) {
                    // Error cross-origin — caer al redirect directo
                }
            }

            // Sin opener: redirigir en la misma pestaña al lanzador.
            window.location.href = launcherUrl;
        })();
    </script>
</body>
</html>
