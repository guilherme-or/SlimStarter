<?php

declare(strict_types=1);

namespace App\Domain\Credential;

interface CredentialRepository
{
    /**
     * @throws InvalidCredentialException | CredentialNotFoundException
     */
    public function authenticate(string $username, string $password): Credential;

    public function destroy() : void;
}
