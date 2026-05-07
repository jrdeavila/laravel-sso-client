<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cerrando sesión…</title>
    <style>
        :root {
            --accent:     #0A7AFF;
            --accent-mid: #3B9EFF;
            --accent-10:  rgba(10,122,255,.10);
            --accent-20:  rgba(10,122,255,.20);
            --dot:        rgba(10,122,255,.08);
            --g50:   #F5F8FF;
            --g400:  #94A3B8;
            --g600:  #475569;
            --g900:  #0B1E3F;
            --white: #FFFFFF;
            --r-xl:  20px;
            --ease:  cubic-bezier(.4,0,.2,1);
            --bounce: cubic-bezier(.34,1.56,.64,1);
        }
        html.dark {
            --accent-10: rgba(10,122,255,.18);
            --accent-20: rgba(10,122,255,.28);
            --dot:   rgba(10,122,255,.06);
            --g50:   #0B1120;
            --g400:  #64748b;
            --g600:  #94a3b8;
            --g900:  #f1f5f9;
            --white: #141d2e;
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
            box-shadow: 0 4px 32px rgba(10,122,255,.13), 0 1px 4px rgba(0,0,0,.06);
            padding: 52px 44px 44px;
            text-align: center;
            max-width: 400px; width: 90%;
            animation: rise .5s var(--bounce) both;
            overflow: hidden;
        }
        .card::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; height: 4px;
            background: linear-gradient(90deg, var(--accent), var(--accent-mid));
            border-radius: var(--r-xl) var(--r-xl) 0 0;
        }
        .card::after {
            content: '';
            position: absolute; bottom: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, var(--accent), var(--accent-mid));
            border-radius: 0 0 var(--r-xl) var(--r-xl);
            animation: indeterminate 1.8s ease-in-out infinite;
        }
        @keyframes indeterminate {
            0%   { transform: translateX(-100%) scaleX(.4); }
            50%  { transform: translateX(30%) scaleX(.6); }
            100% { transform: translateX(100%) scaleX(.4); }
        }

        .spinner-wrap {
            position: relative;
            width: 88px; height: 88px;
            margin: 0 auto 28px;
        }
        .ring {
            position: absolute; inset: 0;
            border-radius: 50%;
            border: 3px solid transparent;
        }
        .ring-outer {
            border-top-color: var(--accent);
            border-right-color: var(--accent-20);
            animation: spin 1s cubic-bezier(.5,.15,.5,.85) infinite;
        }
        .ring-inner {
            inset: 10px;
            border-bottom-color: var(--accent-mid);
            border-left-color: var(--accent-10);
            animation: spin .7s cubic-bezier(.5,.15,.5,.85) infinite reverse;
        }
        /* Exit door icon centered */
        .ring-center {
            position: absolute; inset: 0;
            display: flex; align-items: center; justify-content: center;
        }

        h2 {
            font-size: 1.2rem; font-weight: 700;
            color: var(--g900);
            margin-bottom: 6px;
            letter-spacing: -.01em;
        }
        .subtitle {
            font-size: .875rem; color: var(--g600);
            display: flex; align-items: center; justify-content: center; gap: 2px;
        }
        .subtitle .d { animation: blink .9s ease-in-out infinite; }
        .subtitle .d:nth-child(2) { animation-delay: .2s; }
        .subtitle .d:nth-child(3) { animation-delay: .4s; }

        .farewell {
            display: inline-block;
            margin-top: 20px;
            background: var(--accent-10);
            color: var(--accent);
            font-size: .8rem; font-weight: 600;
            padding: 6px 14px; border-radius: 99px;
            border: 1px solid var(--accent-20);
            animation: pulse-badge 2s ease-in-out infinite;
        }

        @keyframes rise       { from { opacity:0; transform:translateY(24px) scale(.97); } to { opacity:1; transform:none; } }
        @keyframes spin       { to { transform: rotate(360deg); } }
        @keyframes blink      { 0%,100%{opacity:.2} 50%{opacity:1} }
        @keyframes pulse-badge { 0%,100%{opacity:1} 50%{opacity:.65} }
    </style>
    <script>(function(){if(localStorage.getItem('dark-mode')==='1')document.documentElement.classList.add('dark');})();</script>
</head>
<body>
    <div class="card">
        <div class="spinner-wrap">
            <div class="ring ring-outer"></div>
            <div class="ring ring-inner"></div>
            <div class="ring-center">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none"
                     stroke="var(--accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
            </div>
        </div>
        <h2>Cerrando sesión</h2>
        <p class="subtitle">Regresando al lanzador<span class="d">.</span><span class="d">.</span><span class="d">.</span></p>
        <span class="farewell">👋 ¡Hasta pronto!</span>
    </div>

    <script>
        (function () {
            var launcherUrl = @json($launcher_url);

            function goToLauncher(launcherWin) {
                if (launcherWin) { try { launcherWin.focus(); } catch (e) {} }
                window.close();
                setTimeout(function () { window.location.href = launcherUrl; }, 500);
            }

            if (window.opener && !window.opener.closed) {
                try { goToLauncher(window.opener); return; } catch (e) {}
            }

            var launcherWin = null;
            try { launcherWin = window.open('', 'sso-launcher'); } catch (e) {}

            if (launcherWin && launcherWin !== window && !launcherWin.closed) {
                var launcherIsOpen = false;
                try { launcherIsOpen = launcherWin.location.href !== 'about:blank'; }
                catch (e) { launcherIsOpen = true; }
                if (launcherIsOpen) { goToLauncher(launcherWin); return; }
                else { launcherWin.close(); }
            }

            window.location.href = launcherUrl;
        })();
    </script>
</body>
</html>
