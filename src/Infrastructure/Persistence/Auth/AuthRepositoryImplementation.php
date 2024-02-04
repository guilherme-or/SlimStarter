<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Auth;

use App\Domain\Auth\AuthRepository;

class AuthRepositoryImplementation implements AuthRepository
{
    // Declare dependencies to be autowired in the constructor
    public function __construct()
    {
    }

    // Use this class to implement repository interface methods
}
