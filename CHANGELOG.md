# Changelog

## [1.2.1] - 2026-04-27

### Mejorado
- `SsoAuthenticate` marca `sso_public_path=true` en `$request->attributes` para rutas públicas
- Si un middleware `auth` de la ruta bloquea una ruta pública, se devuelve `HTTP 401` en vez de redirigir al lanzador
- Vista `sso-client::public-path-auth-conflict` con instrucción de corrección para el desarrollador

### Sin cambios (backward compatible con v1.2.0)
- Comportamiento anterior idéntico cuando `auth` no está presente en rutas públicas
- Todos los endpoints y middleware existentes — sin modificaciones

## [1.2.0] - 2026-04-27

### Agregado
- Endpoint `GET /sso/local-public-paths` registrado automáticamente por el paquete
- El lanzador lo consulta desde Admin > Aplicativos > Ver detalle > "Cargar rutas del receptor"
- Autenticado con service token (`sub=0`) firmado con el secret de la app receptora
- Devuelve el array `config('sso.public_paths', [])` de la app receptora

### Sin cambios (backward compatible con v1.1.x)
- Todos los endpoints y middleware existentes — sin modificaciones
- `config/sso.php` — `public_paths` ya existía desde v1.1.x

## [1.1.0] - 2026-04-07

### Agregado
- Feature opcional: Widget Provider
- `config/widgets.php` publicable para declarar widgets por configuración
- `WidgetRegistry` — resuelve widgets desde config, expone manifiesto público
- `WidgetController` — endpoints `show()` y `manifest()`
- `routes/widgets.php` — rutas protegidas por `sso.token` (ValidateSsoToken)
- Layout Blade mínimo `sso-client::widgets.layout` sin chrome de AdminLTE
- Widget de ejemplo `sso-client::widgets.example.chatbot` con postMessage
- Tags publicables: `ccv-widgets-config`, `ccv-widgets-views`

### Sin cambios (backward compatible con v1.0.0)
- `ValidateSsoToken` middleware — sin modificaciones
- `SsoTokenService` — sin modificaciones
- `routes/sso.php` y endpoint `POST /sso/verify-secret` — sin modificaciones
- `config/sso.php` — sin modificaciones

## [1.0.0] - 2026-03-27

### Added
- `Crypto/SsoSigner` — algoritmo portado de `App\Services\SsoTokenService` (lanzador)
- `SsoTokenService` con `decode()` e `isValid()`
- `ValidateSsoToken` middleware — alias `sso.token`
- `SsoController@handleCallback` — `User::find()` en DB compartida
- Auto-discovery vía `SsoClientServiceProvider`
- Config publicable con tag `sso-config`
- Workflow GitHub Actions para notificar Private Packagist en tags `v*`
