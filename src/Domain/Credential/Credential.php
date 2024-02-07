<?php

declare(strict_types=1);

namespace App\Domain\Credential;

use App\Domain\User\User;
use DateTime;
use JsonSerializable;

class Credential implements JsonSerializable
{
    private ?int $id;
    private string $username;
    private DateTime $loginTime;

    public function __construct(User $user)
    {
        $this->id = $user->getId();
        $this->username = $user->getUsername();
        $this->loginTime = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getUsername(): string
    {
        return $this->username ?? null;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'username' => $this->getUsername(),
            'loginTime' => $this->loginTime->format(DateTime::ATOM),
        ];
    }
}
