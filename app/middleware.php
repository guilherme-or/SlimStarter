<?php

declare(strict_types=1);

use App\Application\Middleware\BodyCleanerMiddleware;
use Slim\App;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Tuupola\Middleware\JwtAuthentication;

return function (App $app) {
    $jwt = $app->getContainer()->get(JwtAuthentication::class);

    $app->add($jwt);
    $app->add(BodyCleanerMiddleware::class);
    $app->add(TwigMiddleware::createFromContainer($app, Twig::class));
};
