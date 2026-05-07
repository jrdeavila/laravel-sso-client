<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso denegado</title>
    <style>
        :root {
            --accent:    #DC2626;
            --accent-mid:#F87171;
            --accent-10: rgba(220,38,38,.08);
            --accent-20: rgba(220,38,38,.18);
            --dot:       rgba(220,38,38,.05);
            --g50:   #FFF8F8;
            --g400:  #94A3B8;
            --g600:  #475569;
            --g900:  #0B1E3F;
            --white: #FFFFFF;
            --r-xl:  20px;
            --ease:  cubic-bezier(.4,0,.2,1);
            --bounce: cubic-bezier(.34,1.56,.64,1);
        }
        html.dark {
            --accent-10: rgba(220,38,38,.14);
            --accent-20: rgba(220,38,38,.26);
            --dot:       rgba(220,38,38,.04);
            --g50:   #160B0B;
            --g400:  #64748b;
            --g600:  #94a3b8;
            --g900:  #f1f5f9;
            --white: #1e1414;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: var(--g50);
            color: var(--g600);
            display: flex; align-items: center; justify-content: center;
            min-height: 100vh;
            transition: background .35s var(--ease);
            overflow: hidden;
        }
        body::before {
            content: '';
            position: fixed; inset: 0; z-index: 0;
            background-image: radial-gradient(circle, var(--dot) 1.5px, transparent 1.5px);
            background-size: 28px 28px;
            pointer-events: none;
        }
        .card {
            position: relative; z-index: 1;
            background: var(--white);
            border-radius: var(--r-xl);
            box-shadow: 0 4px 32px rgba(220,38,38,.10), 0 1px 4px rgba(0,0,0,.06);
            padding: 52px 44px 44px;
            text-align: center;
            max-width: 420px; width: 90%;
            animation: rise .5s var(--bounce) both;
            overflow: hidden;
        }
        .card::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; height: 4px;
            background: linear-gradient(90deg, var(--accent), var(--accent-mid));
            border-radius: var(--r-xl) var(--r-xl) 0 0;
        }
        /* Countdown progress bar */
        .progress {
            position: absolute; bottom: 0; left: 0; height: 3px;
            background: linear-gradient(90deg, var(--accent), var(--accent-mid));
            border-radius: 0 0 0 var(--r-xl);
            width: 100%;
            transform-origin: left;
            animation: shrink 3s linear forwards;
        }
        @keyframes shrink { to { transform: scaleX(0); } }

        /* Icon with pulse rings */
        .icon-wrap {
            position: relative;
            width: 88px; height: 88px;
            margin: 0 auto 28px;
            display: flex; align-items: center; justify-content: center;
        }
        .pulse-ring {
            position: absolute; inset: 0;
            border-radius: 50%;
            border: 2px solid var(--accent-20);
            animation: pulse-ring 2.4s ease-out infinite;
        }
        .pulse-ring:nth-child(2) { animation-delay: .8s; }
        .icon-bg {
            position: relative; z-index: 1;
            width: 72px; height: 72px;
            border-radius: 50%;
            background: var(--accent-10);
            display: flex; align-items: center; justify-content: center;
            border: 1.5px solid var(--accent-20);
        }
        .icon-bg svg {
            animation: lock-shake .6s ease .4s both;
        }

        h2 {
            font-size: 1.3rem; font-weight: 800;
            color: var(--g900);
            margin-bottom: 6px;
            letter-spacing: -.02em;
        }
        .desc {
            font-size: .9rem; color: var(--g600);
            line-height: 1.6;
            margin-bottom: 16px;
        }
        .app-pill {
            display: inline-flex; align-items: center; gap: 6px;
            background: var(--accent-10);
            border: 1px solid var(--accent-20);
            color: var(--accent);
            font-size: .82rem; font-weight: 700;
            padding: 5px 14px; border-radius: 99px;
            margin-bottom: 24px;
        }
        .app-pill svg { width: 13px; height: 13px; flex-shrink: 0; }

        .countdown-row {
            display: flex; align-items: center; justify-content: center;
            gap: 8px;
            font-size: .8rem; color: var(--g400);
        }
        .countdown-row .spinner-sm {
            width: 14px; height: 14px;
            border-radius: 50%;
            border: 2px solid var(--accent-20);
            border-top-color: var(--accent);
            animation: spin .9s linear infinite;
            flex-shrink: 0;
        }
        #countdown-num {
            font-weight: 700; color: var(--accent);
            min-width: 10px; display: inline-block;
        }

        @keyframes rise      { from { opacity:0; transform:translateY(24px) scale(.97); } to { opacity:1; transform:none; } }
        @keyframes spin      { to { transform: rotate(360deg); } }
        @keyframes pulse-ring {
            0%   { transform: scale(1);   opacity: 1; }
            100% { transform: scale(1.7); opacity: 0; }
        }
        @keyframes lock-shake {
            0%,100% { transform: rotate(0); }
            15%     { transform: rotate(-10deg) translateY(-2px); }
            30%     { transform: rotate(8deg)  translateY(-3px); }
            45%     { transform: rotate(-6deg) translateY(-1px); }
            60%     { transform: rotate(4deg); }
            75%     { transform: rotate(-2deg); }
        }
    </style>
    <script>(function(){if(localStorage.getItem('dark-mode')==='1')document.documentElement.classList.add('dark');})();</script>
</head>
<body>
    <div class="card">
        <div class="progress" id="progress-bar"></div>

        <div class="icon-wrap">
            <div class="pulse-ring"></div>
            <div class="pulse-ring"></div>
            <div class="icon-bg">
                <svg width="30" height="30" viewBox="0 0 24 24" fill="none"
                     stroke="#DC2626" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
            </div>
        </div>

        <h2>Acceso denegado</h2>
        <p class="desc">No tienes permisos para acceder a</p>
        <div class="app-pill">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
            </svg>
            {{ $app_name ?? 'esta aplicación' }}
        </div>

        <div class="countdown-row">
            <div class="spinner-sm"></div>
            Cerrando pestaña en <span id="countdown-num">3</span>s…
        </div>
    </div>

    <script>
        (function () {
            var appName     = @json($app_name ?? null);
            var launcherUrl = @json(rtrim($launcher_url ?? config('sso.launcher_url', '/'), '/'));

            /* Countdown visual */
            var n = 3, el = document.getElementById('countdown-num');
            var tick = setInterval(function () {
                n--;
                if (el) el.textContent = Math.max(n, 0);
                if (n <= 0) clearInterval(tick);
            }, 1000);

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
                } else { launcherWin.close(); }
            }

            window.location.href = launcherUrl + '?error=sso_unauthorized';
        })();
    </script>
</body>
</html>
