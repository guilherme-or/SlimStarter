<?php

declare(strict_types=1);

namespace App\Domain\Auth;

use App\Domain\DomainException\DomainRecordNotFoundException;

class AuthNotFoundException extends DomainRecordNotFoundException
{
    public $message = "The auth you requested does not exist.";
}
