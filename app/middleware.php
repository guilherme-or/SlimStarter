<?php

declare(strict_types=1);

// use App\Application\Middleware\SessionMiddleware;
use Odan\Session\Middleware\SessionStartMiddleware;
use Slim\App;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

return function (App $app) {
    $app->add(SessionStartMiddleware::class);
    $app->add(TwigMiddleware::createFromContainer($app, Twig::class));
};
