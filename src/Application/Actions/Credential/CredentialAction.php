<?php

declare(strict_types=1);

namespace App\Application\Actions\Credential;

use App\Adapter\Database\DatabaseConnectionInterface;
use App\Application\Actions\Action;
use App\Domain\Credential\CredentialRepository;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;
use voku\helper\AntiXSS;

abstract class CredentialAction extends Action
{
    public function __construct(
        protected DatabaseConnectionInterface $connection,
        protected Twig $twig,
        protected LoggerInterface $logger,
        protected AntiXSS $antiXss,
        protected CredentialRepository $credentialRepository
    ) {
    }
}
