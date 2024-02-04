<?php

declare(strict_types=1);

namespace App\Domain\Auth;

use App\Domain\Entity;
use JsonSerializable;

class Auth implements Entity, JsonSerializable
{
    public function __construct()
    {
    }

    public function __get(string $name)
    {
        return property_exists($this, $name) ? $this->$name : null;
    }

    public function __set(string $name, $value): void
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        }
    }

    public static function fromJson(array $json): Auth
    {
        return new Auth();
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        return [];
    }
}
