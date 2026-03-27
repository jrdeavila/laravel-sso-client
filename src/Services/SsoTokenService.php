<?php

namespace CamaradeComercioDeValledupar\SsoClient\Services;

use CamaradeComercioDeValledupar\SsoClient\Crypto\SsoSigner;

class SsoTokenService
{
    public function __construct(private readonly SsoSigner $signer) {}

    /**
     * Decodifica y valida el token. Retorna payload como stdClass.
     *
     * @throws \RuntimeException Si el token es inválido o expirado
     */
    public function decode(string $token): \stdClass
    {
        return (object) $this->signer->decode($token);
    }

    public function isValid(string $token): bool
    {
        try {
            $this->decode($token);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
