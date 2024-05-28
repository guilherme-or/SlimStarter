<?php

declare(strict_types=1);

namespace App\Domain\Credential;

class CredentialToken
{
    public const TOKEN_NAME = 'token';

    public function __construct(
        private string $token,
        private CredentialPayload $payload
    ) {
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getPayload(): CredentialPayload
    {
        return $this->payload;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        return [
            "token" => $this->token,
            "payload" => $this->payload
        ];
    }
}