<?php

declare(strict_types=1);

namespace App\Domain\User;

use JsonSerializable;

class User implements JsonSerializable
{
    private ?int $id;

    private string $username;

    private string $hash;


    public function __construct(
        ?int $id,
        string $username,
        string $hash,
    ) {
        $this->id = $id;
        $this->username = strtolower($username);
        $this->hash = $hash;
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
