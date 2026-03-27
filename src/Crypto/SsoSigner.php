<?php

namespace CamaradeComercioDeValledupar\SsoClient\Crypto;

/**
 * Formato: base64(json(payload)).hmac_sha256(base64(json(payload)), secret)
 * Son DOS partes separadas por punto — no el JWT estándar de tres partes.
 *
 * Portado de App\Services\SsoTokenService — camara-de-comercio-de-valledupar/laravel-sso-client v1.0.0
 *
 * IMPORTANTE: encode() y decode() deben mantenerse sincronizados con
 * el lanzador. Cualquier cambio debe coordinarse y versionarse.
 * Una divergencia en el algoritmo rompe toda la integración SSO.
 */
class SsoSigner
{
    public function __construct(private readonly ?string $secret) {}

    /**
     * Construye el token.
     * Portado de App\Services\SsoTokenService — no modificar sin versionar.
     *
     * Formato resultante: base64(json(payload)).hmac_sha256(base64(json(payload)), secret)
     */
    public function encode(array $payload): string
    {
        if ($this->secret === null) {
            throw new \RuntimeException('SSO_SECRET no está configurado.');
        }

        $payloadEncoded = base64_encode(json_encode($payload));
        $signature      = hash_hmac('sha256', $payloadEncoded, $this->secret);

        return $payloadEncoded . '.' . $signature;
    }

    /**
     * Valida firma y expiración; retorna el payload decodificado.
     * Portado de App\Services\SsoTokenService — no modificar sin versionar.
     *
     * @throws \RuntimeException Si el token es malformado, la firma no coincide,
     *                           el payload es inválido, o el token está expirado.
     */
    public function decode(string $token): array
    {
        $parts = explode('.', $token, 2);

        if (count($parts) !== 2) {
            throw new \RuntimeException('Token malformado.');
        }

        if ($this->secret === null) {
            throw new \RuntimeException('SSO_SECRET no está configurado.');
        }

        [$payloadEncoded, $signature] = $parts;
        $expected = hash_hmac('sha256', $payloadEncoded, $this->secret);

        if (! hash_equals($expected, $signature)) {
            throw new \RuntimeException('Firma inválida.');
        }

        $payload = json_decode(base64_decode($payloadEncoded), true);

        if (! $payload || ! isset($payload['sub'], $payload['exp'])) {
            throw new \RuntimeException('Payload inválido o campos requeridos ausentes.');
        }

        if ($payload['exp'] < time()) {
            throw new \RuntimeException('Token expirado.');
        }

        return $payload;
    }
}
