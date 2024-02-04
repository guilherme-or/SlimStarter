<?php

declare(strict_types=1);

namespace App\Application\Actions\User;

use App\Application\Actions\Action;
use App\Domain\User\UserRepository;
use App\Infrastructure\Database\Connection;
use Odan\Session\SessionInterface;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;
use voku\helper\AntiXSS;

abstract class UserAction extends Action
{
    protected UserRepository $userRepository;

    public function __construct(
        SessionInterface $session,
        Connection $connection,
        Twig $twig,
        LoggerInterface $logger,
        AntiXSS $antiXss,
        UserRepository $userRepository
    ) {
        parent::__construct(
            $session,
            $connection,
            $twig,
            $logger,
            $antiXss
        );
        $this->userRepository = $userRepository;
    }
}
