<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Routing\RouteContext;

class UserMiddleware implements Middleware
{
    private const CREDENTIALS_PAGE_ROUTE_NAME = "credentials.page";

    /**
     * {@inheritdoc}
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        if (!isset($_SESSION['user'])) {
            if ($request->getMethod() !== "GET") {
                throw new HttpUnauthorizedException($request, "User not logged");
            }

            $url = RouteContext::fromRequest($request)
                ->getRouteParser()
                ->urlFor(self::CREDENTIALS_PAGE_ROUTE_NAME);

            $response = new \Slim\Psr7\Response();
            return $response
                ->withStatus(301)
                ->withHeader('Location', $url);
        }

        return $handler->handle($request);
    }
}
