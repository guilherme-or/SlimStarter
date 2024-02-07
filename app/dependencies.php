<?php

declare(strict_types=1);

use App\Application\Settings\SettingsInterface;
use App\Application\Views\Extensions\AssetsExtension;
use App\Infrastructure\Database\Connection;
use App\Infrastructure\Database\ConnectionInterface;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Odan\Session\PhpSession;
use Odan\Session\SessionInterface;
use Odan\Session\SessionManagerInterface;
use Odan\Session\FlashInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;
use voku\helper\AntiXSS;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        SessionInterface::class => function (ContainerInterface $c) {
            /** @var SettingsInterface $settings */
            $settings = $c->get(SettingsInterface::class);

            /** @var array */
            $sessionSettings = $settings->get('session');

            return new PhpSession($sessionSettings);
        },

        SessionManagerInterface::class => function (ContainerInterface $c) {
            return $c->get(SessionInterface::class);
        },

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

        ConnectionInterface::class => function (ContainerInterface $c) {
            /** @var SettingsInterface $settings */
            $settings = $c->get(SettingsInterface::class);

            /** @var array */
            $databaseSettings = $settings->get('database');

            return Connection::createFromSettings($databaseSettings);
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

            /** @var FlashInterface */
            $flash = $c->get(SessionInterface::class)->getFlash();
            $twigEnv->addGlobal('flash', $flash);

            $twigEnv->addGlobal('language', $settings->get('language'));

            return $twig;
        },

        AntiXSS::class => function (ContainerInterface $c) {
            return new AntiXSS();
        },
    ]);
};
