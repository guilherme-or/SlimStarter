<?php

declare(strict_types=1);

namespace App\Application\Actions\Credential;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Routing\RouteContext;

class DispatchAction extends CredentialAction
{
    private const LOGIN_PAGE_ROUTE_NAME = "credentials.page";
    private const HOME_PAGE_ROUTE_NAME = "home.page";

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $jwt = (array) $this->request->getAttribute('jwt');

        $routeName = isset($jwt['user']) ? self::HOME_PAGE_ROUTE_NAME : self::LOGIN_PAGE_ROUTE_NAME;

        $url = RouteContext::fromRequest($this->request)
            ->getRouteParser()
            ->urlFor($routeName);

        return $this->response
            ->withStatus(301)
            ->withHeader('Location', $url);
    }
}
