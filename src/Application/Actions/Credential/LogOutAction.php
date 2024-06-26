<?php

declare(strict_types=1);

namespace App\Application\Actions\Credential;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Routing\RouteContext;

class LogOutAction extends CredentialAction
{
    private const LOGIN_ROUTE_NAME = "credentials.login";

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $this->credentialRepository->destroy();

        $url = RouteContext::fromRequest($this->request)
            ->getRouteParser()
            ->urlFor(self::LOGIN_ROUTE_NAME);

        return $this->response
            ->withStatus(301)
            ->withHeader('Location', $url);
    }
}
