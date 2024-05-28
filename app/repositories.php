<?php

declare(strict_types=1);

use App\Domain\Credential\CredentialRepository;
use App\Domain\User\UserRepository;
use App\Infrastructure\Persistence\Credential\CredentialRepositoryImplementation;
use App\Infrastructure\Persistence\User\UserRepositoryImplementation;
use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
    // Here we map our UserRepository interface to its in memory implementation
    $containerBuilder->addDefinitions([
        UserRepository::class => \DI\autowire(UserRepositoryImplementation::class),
        CredentialRepository::class => \DI\autowire(CredentialRepositoryImplementation::class)
    ]);
};
