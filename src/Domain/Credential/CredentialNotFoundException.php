<?php

declare(strict_types=1);

namespace App\Domain\Credential;

use App\Domain\DomainException\DomainRecordNotFoundException;

class CredentialNotFoundException extends DomainRecordNotFoundException
{
    public $message = "Unknown username or incorrect password";
}
