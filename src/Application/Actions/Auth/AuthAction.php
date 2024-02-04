<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use App\Application\Actions\Action;
use App\Domain\Auth\AuthRepository;

abstract class AuthAction extends Action
{
    protected AuthRepository $authRepository;

    public function __construct(
        AuthRepository $authRepository
    ) {
        parent::__construct();
        $this->authRepository = $authRepository;
    }
}
