<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Domain\DomainException\DomainRecordNotFoundException;

class UserInvalidationException extends DomainRecordNotFoundException
{
    public $message = 'Invalid user argument(s)';
}
