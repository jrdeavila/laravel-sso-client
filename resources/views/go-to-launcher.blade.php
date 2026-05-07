<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirigiendo al lanzador…</title>
    <style>
        :root {
            --blue:   #0A7AFF;
            --g50:    #F5F8FF;
            --g200:   #E2E8F0;
            --g400:   #94A3B8;
            --g600:   #475569;
            --g900:   #0B1E3F;
            --white:  #FFFFFF;
            --r-xl:   20px;
            --shadow: 0 2px 24px rgba(10,122,255,.10);
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
            max-width: 380px;
            width: 90%;
            animation: rise .4s var(--ease) both;
        }
        .icon-wrap {
            width: 72px; height: 72px;
            border-radius: 50%;
            background: rgba(10,122,255,.08);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 24px;
        }
        .icon-wrap svg { animation: spin 1.2s linear infinite; }
        h2 {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--g900);
            margin-bottom: 8px;
        }
        p { font-size: .875rem; line-height: 1.6; color: var(--g600); }
        .dots::after {
            content: '';
            animation: dots 1.5s steps(4, end) infinite;
        }
        @keyframes spin  { to { transform: rotate(360deg); } }
        @keyframes rise  { from { opacity:0; transform:translateY(18px); } to { opacity:1; transform:none; } }
        @keyframes dots  { 0%,20%{content:''}  40%{content:'.'}  60%{content:'..'}  80%,100%{content:'...'} }
    </style>
    <script>(function(){if(localStorage.getItem('dark-mode')==='1')document.documentElement.classList.add('dark');})();</script>
</head>
<body>
    <div class="card">
        <div class="icon-wrap">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none"
                 stroke="#0A7AFF" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
            </svg>
        </div>
        <h2>Redirigiendo al lanzador<span class="dots"></span></h2>
        <p>Un momento, te estamos llevando de vuelta.</p>
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
