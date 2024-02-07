<?php

declare(strict_types=1);

namespace App\Domain\Credential;

use App\Domain\DomainException\DomainInvalidRecordException;

class InvalidCredentialException extends DomainInvalidRecordException
{
    public $message = "The given username or password is not a valid value.";
}
