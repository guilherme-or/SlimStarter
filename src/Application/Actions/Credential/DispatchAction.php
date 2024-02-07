<?php

declare(strict_types=1);

namespace App\Application\Actions\Credential;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Routing\RouteContext;

class DispatchAction extends CredentialAction
{
    private const HOME_PAGE_ROUTE_NAME = "home.page";

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        // Define $routeName by looking into user scope ($_SESSION['user'])
        $routeName = self::HOME_PAGE_ROUTE_NAME;

        $url = RouteContext::fromRequest($this->request)
            ->getRouteParser()
            ->urlFor($routeName);
        
        return $this->response
            ->withStatus(301)
            ->withHeader('Location', $url);
    }
}
