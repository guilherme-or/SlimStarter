<?php

declare(strict_types=1);

namespace App\Domain\User;

use JsonSerializable;

class User implements JsonSerializable
{
    public function __construct(
        private ?int $id,
        private string $username,
        private string $hash,
    ) {
        $this->validate();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    private function validate(): void
    {
        if (empty($this->username) || empty($this->hash)) {
            throw new UserInvalidationException("Invalid username or hash");
        }
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'hash' => $this->hash
        ];
    }
}
