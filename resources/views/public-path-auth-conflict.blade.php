<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ruta pública bloqueada por auth middleware</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: system-ui, -apple-system, sans-serif;
            display: flex; align-items: center; justify-content: center;
            min-height: 100vh; background: #f8fafc; color: #334155;
        }
        .card {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
            padding: 2rem 2.5rem; max-width: 480px; width: 100%; text-align: center;
            box-shadow: 0 1px 6px rgba(0,0,0,.06);
        }
        .icon { font-size: 2.5rem; margin-bottom: 1rem; }
        h1 { font-size: 1.1rem; font-weight: 600; color: #0f172a; margin-bottom: .5rem; }
        p { font-size: .9rem; color: #64748b; line-height: 1.6; margin-bottom: .75rem; }
        code {
            background: #f1f5f9; padding: .15em .4em; border-radius: 4px;
            font-family: ui-monospace, monospace; font-size: .85em; color: #be185d;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">⚠️</div>
        <h1>Ruta pública bloqueada</h1>
        <p>
            Esta ruta está declarada en <code>sso.public_paths</code>, por lo que
            <code>SsoAuthenticate</code> la permite sin sesión. Sin embargo, el
            middleware <code>auth</code> de la ruta está bloqueando la petición.
        </p>
        <p>
            <strong>Solución:</strong> quita <code>->middleware('auth')</code>
            (o el grupo <code>auth</code>) de las rutas declaradas en
            <code>public_paths</code>. <code>SsoAuthenticate</code> protege el resto
            automáticamente.
        </p>
    </div>
</body>
</html>
