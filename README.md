# camara-de-comercio-de-valledupar/laravel-sso-client

SSO client package for Laravel receiver applications for Cámara de Comercio de Valledupar.

---

## Requisitos

- PHP 8.2+
- Laravel 11 o Laravel 12
- Acceso a la base de datos compartida de usuarios de Cámara de Comercio de Valledupar (conexión configurada en la receptora)

---

## Instalación

```bash
composer require camara-de-comercio-de-valledupar/laravel-sso-client

# Opcional — publicar config para sobrescribir valores por defecto:
php artisan vendor:publish --tag=sso-config
```

El Service Provider se registra automáticamente vía package auto-discovery.

---

## Variables de entorno requeridas

| Variable | Descripción | Ejemplo |
|---|---|---|
| `SSO_SECRET` | Secret compartido con el lanzador | (coordinarlo con el administrador del lanzador) |
| `SSO_LAUNCHER_URL` | URL base del lanzador | `https://lanzador.ccvalledupar.org.co` |
| `SSO_TOKEN_TTL` | TTL del token en segundos | `60` |
| `SSO_REDIRECT_AFTER_LOGIN` | Ruta destino tras login exitoso | `/dashboard` |

Las variables `SSO_TOKEN_PARAM` (default: `token`) y `SSO_USER_ID_FIELD` (default: `sub`) ya tienen los valores correctos para trabajar con el lanzador. Solo sobreescribir si se cambia la configuración en el lanzador.

---

## Lo que el paquete registra automáticamente

- **Ruta** `GET /sso/callback` (named `sso.callback`)
- **Middleware alias** `sso.token` → `ValidateSsoToken`
- **Config** `sso.*` (publicable con tag `sso-config`)
- No hay migraciones — la base de datos de usuarios ya es compartida entre el lanzador y las receptoras

---

## Flujo completo

```
Usuario hace clic en el lanzador
↓
Lanzador genera token (TTL: 60s)
payload: { sub: 42, app: "slug", iat: ..., exp: ... }
↓
Redirect → https://receptora.com/sso/callback?token=xxx
↓
Middleware ValidateSsoToken → valida firma HMAC-SHA256 y expiración
↓
SsoController → User::find(42) en DB compartida
↓
Auth::login($user) → redirect a SSO_REDIRECT_AFTER_LOGIN
```

**Formato del token:** `base64(json(payload)).hmac_sha256(base64(json(payload)), secret)`
Son dos partes separadas por punto — no es JWT estándar de tres partes.

---

## Errores comunes

| URL recibida | Causa | Solución |
|---|---|---|
| `?error=sso_invalid` | Secret distinto o token malformado | Verificar que `SSO_SECRET` coincide con el del lanzador |
| `?error=sso_failed` | Token expirado (> 60s) o inválido | El usuario debe volver al lanzador y reintentar |
| `?error=sso_user_not_found` | El ID del payload no existe en la DB | Verificar la conexión a la base de datos compartida |
