<?php

declare(strict_types=1);

namespace App\Application\Actions\Credential;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Routing\RouteContext;

class CredentialPageAction extends CredentialAction
{
    private const DISPATCHER_ROUTE_NAME = "credentials.dispatcher";
    private const CREDENTIAL_PAGE_TEMPLATE = "Credential/page.html";

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $jwt = $this->request->getAttribute("jwt");

        if (isset($jwt['user'])) {
            $url = RouteContext::fromRequest($this->request)
                ->getRouteParser()
                ->urlFor(self::DISPATCHER_ROUTE_NAME);

            return $this->response
                ->withStatus(301)
                ->withHeader('Location', $url);
        }

        return $this->renderTemplate(self::CREDENTIAL_PAGE_TEMPLATE);
    }
}
