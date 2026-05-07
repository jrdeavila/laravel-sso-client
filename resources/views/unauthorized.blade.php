<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso denegado</title>
    <style>
        :root {
            --red:    #DC2626;
            --red-10: rgba(220,38,38,.08);
            --g50:    #F5F8FF;
            --g200:   #E2E8F0;
            --g400:   #94A3B8;
            --g600:   #475569;
            --g900:   #0B1E3F;
            --white:  #FFFFFF;
            --r-xl:   20px;
            --shadow: 0 2px 24px rgba(0,0,0,.08);
            --ease:   cubic-bezier(.4,0,.2,1);
        }
        html.dark {
            --g50:    #0f172a;
            --g200:   #334155;
            --g400:   #64748b;
            --g600:   #94a3b8;
            --g900:   #f1f5f9;
            --white:  #1e293b;
            --shadow: 0 2px 24px rgba(0,0,0,.35);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: var(--g50);
            color: var(--g600);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            transition: background .3s var(--ease);
        }
        .card {
            background: var(--white);
            border-radius: var(--r-xl);
            box-shadow: var(--shadow);
            padding: 48px 40px;
            text-align: center;
            max-width: 400px;
            width: 90%;
            animation: rise .4s var(--ease) both;
        }
        .icon-wrap {
            width: 72px; height: 72px;
            border-radius: 50%;
            background: var(--red-10);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 24px;
        }
        h2 {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--g900);
            margin-bottom: 8px;
        }
        p { font-size: .875rem; line-height: 1.6; color: var(--g600); }
        .app-name { font-weight: 600; color: var(--g900); }
        .hint {
            margin-top: 20px;
            font-size: .78rem;
            color: var(--g400);
            display: flex; align-items: center; justify-content: center; gap: 6px;
        }
        .hint svg { animation: spin 1.2s linear infinite; flex-shrink: 0; }
        @keyframes rise { from { opacity:0; transform:translateY(18px); } to { opacity:1; transform:none; } }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
    <script>(function(){if(localStorage.getItem('dark-mode')==='1')document.documentElement.classList.add('dark');})();</script>
</head>
<body>
    <div class="card">
        <div class="icon-wrap">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none"
                 stroke="#DC2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
        </div>
        <h2>Acceso denegado</h2>
        <p>No tienes permisos para acceder a<br><span class="app-name">{{ $app_name ?? 'esta aplicación' }}</span>.</p>
        <p class="hint">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
            </svg>
            Cerrando esta pestaña…
        </p>
    </div>

    <script>
        (function () {
            var appName     = @json($app_name ?? null);
            var launcherUrl = @json(rtrim($launcher_url ?? config('sso.launcher_url', '/'), '/'));

            try {
                localStorage.setItem('sso_notification', JSON.stringify({
                    type: 'access_denied', app: appName, ts: Date.now()
                }));
            } catch (e) {}

            function goToLauncher(launcherWin, extra) {
                if (extra) { try { extra(); } catch (e) {} }
                if (launcherWin) { try { launcherWin.focus(); } catch (e) {} }
                window.close();
                setTimeout(function () { window.location.href = launcherUrl + '?error=sso_unauthorized'; }, 500);
            }

            if (window.opener && !window.opener.closed) {
                try {
                    goToLauncher(window.opener, function () {
                        window.opener.postMessage({ type: 'sso_access_denied', app: appName }, '*');
                    });
                    return;
                } catch (e) {}
            }

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

            window.location.href = launcherUrl + '?error=sso_unauthorized';
        })();
    </script>
</body>
</html>
