<?php

declare(strict_types=1);

namespace App\Application\Actions\User;

use App\Adapter\Database\DatabaseConnectionInterface;
use App\Application\Actions\Action;
use App\Domain\User\UserRepository;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;
use voku\helper\AntiXSS;

abstract class UserAction extends Action
{
    public function __construct(
        protected DatabaseConnectionInterface $connection,
        protected Twig $twig,
        protected LoggerInterface $logger,
        protected AntiXSS $antiXss,
        protected UserRepository $userRepository
    ) {
    }
}
