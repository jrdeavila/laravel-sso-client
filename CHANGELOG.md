# Changelog

## [1.0.0] - 2026-03-27

### Added
- `Crypto/SsoSigner` — algoritmo portado de `App\Services\SsoTokenService` (lanzador)
- `SsoTokenService` con `decode()` e `isValid()`
- `ValidateSsoToken` middleware — alias `sso.token`
- `SsoController@handleCallback` — `User::find()` en DB compartida
- Auto-discovery vía `SsoClientServiceProvider`
- Config publicable con tag `sso-config`
- Workflow GitHub Actions para notificar Private Packagist en tags `v*`
