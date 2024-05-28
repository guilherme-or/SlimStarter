<?php

declare(strict_types=1);

namespace App\Domain\Credential;

interface CredentialRepository
{
    /**
     * @throws CredentialInvalidationException|CredentialNotFoundException
     */
    public function authenticate(string $username, string $password): CredentialToken;

    public function destroy(): void;
}
