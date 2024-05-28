<?php

declare(strict_types=1);

use App\Adapter\Database\DatabaseConnection;
use App\Adapter\Database\DatabaseConnectionInterface;
use App\Application\Settings\SettingsInterface;
use App\Application\Views\Extensions\AssetsExtension;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;
use Tuupola\Middleware\JwtAuthentication;
use voku\helper\AntiXSS;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);

            $loggerSettings = $settings->get('logger');
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },

        DatabaseConnectionInterface::class => function (ContainerInterface $c) {
            /** @var SettingsInterface $settings */
            $settings = $c->get(SettingsInterface::class);

            /** @var array */
            $databaseSettings = $settings->get('database');

            return new DatabaseConnection($databaseSettings);
        },

        Twig::class => function (ContainerInterface $c): Twig {
            /** @var SettingsInterface $settings */
            $settings = $c->get(SettingsInterface::class);

            /** @var array */
            $viewSettings = $settings->get('view');

            $options = [
                'debug' => $settings->get('displayErrorDetails'),
                'cache' => $viewSettings['cache'],
            ];

            $twig = Twig::create($viewSettings['path'], $options);

            $twigEnv = $twig->getEnvironment();
            $twigEnv->addExtension(new AssetsExtension($settings->get('serverPath')));

            $twigEnv->addGlobal('language', $settings->get('language'));

            return $twig;
        },

        AntiXSS::class => function (ContainerInterface $c) {
            return new AntiXSS();
        },

        JwtAuthentication::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);
            $jwtSettings = $settings->get('jwt');

            if (!isset ($jwtSettings['error'])) {
                $jwtSettings['error'] = function (Response $response, array $arguments) use ($c) {
                    $uri = $arguments['uri'] ?? null;
                    $message = $arguments['message'] ?? 'Unauthorized';
                    $statusCode = $message === "Token not found." ? 401 : 403;

                    $data = [
                        'code' => $statusCode,
                        'error' => $message,
                        'description' => "Identifier: '$uri'",
                    ];

                    return $c->get(Twig::class)->render(
                        $response,
                        'Templates/error.html',
                        $data
                    );

                    // $response->getBody()->write(
                    //     json_encode(
                    //         $data,
                    //         JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
                    //     )
                    // );
                    // return $response
                    //     ->withHeader('Content-Type', 'application/json');
                };
            }

            return new JwtAuthentication($jwtSettings);
        },
    ]);
};
