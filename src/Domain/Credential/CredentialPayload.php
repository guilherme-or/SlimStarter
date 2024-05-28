<?php

declare(strict_types=1);

namespace App\Domain\Credential;

use App\Domain\User\User;
use DateTime;
use JsonSerializable;

class CredentialPayload implements JsonSerializable
{
    private DateTime $issuedAt;

    private DateTime $expiresIn;

    /** @var string[] */
    private array $scope;

    public function __construct(
        private User $user
    ) {
        $this->issuedAt = new DateTime();
        $this->expiresIn = (new DateTime())->modify('+30 minutes');
        $this->scope = ['read', 'write'];
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getIssuedTime(): DateTime
    {
        return $this->issuedAt;
    }

    public function getExpirationTime(): DateTime
    {
        return $this->expiresIn;
    }

    /** @return string[] */
    public function getScope(): array
    {
        return $this->scope;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        return [
            'user' => $this->user,
            'iat' => $this->issuedAt->getTimestamp(),
            'exp' => $this->expiresIn->getTimestamp(),
            'scope' => $this->scope,
        ];
    }
}
