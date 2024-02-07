<?php

declare(strict_types=1);

namespace App\Application\Actions\Credential;

use App\Application\Actions\Action;
use App\Domain\Credential\CredentialRepository;
use App\Infrastructure\Database\ConnectionInterface;
use Odan\Session\SessionInterface;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;
use voku\helper\AntiXSS;

abstract class CredentialAction extends Action
{
    protected CredentialRepository $credentialRepository;

    public function __construct(
        SessionInterface $session,
        ConnectionInterface $connection,
        Twig $twig,
        LoggerInterface $logger,
        AntiXSS $antiXss,
        CredentialRepository $credentialRepository
    ) {
        parent::__construct(
            $session,
            $connection,
            $twig,
            $logger,
            $antiXss
        );
        $this->credentialRepository = $credentialRepository;
    }
}
