<?php

declare(strict_types=1);

use App\Application\Settings\Settings;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Monolog\Logger;


$dotenv = Dotenv::createMutable(__DIR__ . "/../");
$requiredValues = [
    'DATABASE_HOST',
    'DATABASE_NAME',
    'DATABASE_USERNAME',
    'DATABASE_PASSWORD',
];

try {
    $dotenv->load();
    $dotenv->required($requiredValues);
} catch (Exception $e) {
    $envExamplePath = realpath(__DIR__ . '/../.env.example');
    $envExamplePath = !$envExamplePath ? '.env.example' : $envExamplePath;
    die("ENVIRONMENT CONFIGURATION ERROR: Check or create a \".env\" file"
        . "based on \"$envExamplePath\" configuration file in your project root."
        . " The following values are required: "
        . implode(", ", $requiredValues));
}


return function (ContainerBuilder $containerBuilder) {

    // Global Settings Object
    $containerBuilder->addDefinitions([
        SettingsInterface::class => function () {
            return new Settings([
                'displayErrorDetails' => true, // Set to false in production
                'logError' => true,
                'logErrorDetails' => true,
                'serverPath' => $_ENV['SERVER_PATH'] ?? null,
                'language' => 'en',
                'jwt' => [
                    'secret' => $_ENV['JWT_SECRET'],
                    'attribute' => 'jwt',
                    'ignore' => ['/login'],
                ],
                'database' => [
                    'host' => $_ENV['DATABASE_HOST'],
                    'dbname' => $_ENV['DATABASE_NAME'],
                    'username' => $_ENV['DATABASE_USERNAME'],
                    'password' => $_ENV['DATABASE_PASSWORD'],
                ],
                'view' => [
                    'path' => __DIR__ . '/../src/Application/Views',
                    'cache' => __DIR__ . '/../var/views'
                ],
                'logger' => [
                    'name' => 'starter',
                    'path' => isset ($_ENV['docker'])
                        ? 'php://stdout'
                        : __DIR__ . '/../logs/app.log',
                    'level' => Logger::DEBUG,
                ],
            ]);
        }
    ]);

};
