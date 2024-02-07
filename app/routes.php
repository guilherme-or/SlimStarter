<?php

declare(strict_types=1);

use App\Application\Actions\Credential\CredentialPageAction;
use App\Application\Actions\Credential\DispatchAction;
use App\Application\Actions\Credential\LogInAction;
use App\Application\Actions\Credential\LogOutAction;
use App\Application\Actions\HomePageAction;
use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use App\Application\Middleware\UserMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/login', CredentialPageAction::class)->setName('credentials.page');
    $app->post('/login', LogInAction::class)->setName('credentials.login');
    $app->post('/logout', LogOutAction::class)->setName('credentials.logout')
        ->add(new UserMiddleware);
    $app->get('/dispatcher', DispatchAction::class)->setName('credentials.dispatcher')
        ->add(new UserMiddleware);

    $app->get('/', HomePageAction::class)->setName('home.page')
        ->add(new UserMiddleware);

    $app->group('/users', function (Group $users) {
        $users->get('', ListUsersAction::class)->setName('users.list');
        $users->get('/{id}', ViewUserAction::class)->setName('users.get');
    })->add(new UserMiddleware);
};
