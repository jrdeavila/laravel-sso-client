# Changelog

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
