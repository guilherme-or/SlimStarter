<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\User;

use App\Domain\User\User;
use App\Domain\User\UserNotFoundException;
use App\Domain\User\UserRepository;
use App\Infrastructure\Database\Connection;
use App\Infrastructure\Database\ConnectionInterface;
use Odan\Session\SessionInterface;
use Psr\Log\LoggerInterface;

class UserRepositoryImplementation implements UserRepository
{
    private SessionInterface $session;
    private LoggerInterface $logger;
    private Connection $connection;

    private const SELECT_ALL = "SELECT `id`, `username`, `hash` FROM users";
    private const SELECT_BY_ID = "SELECT `id`, `username`, `hash` FROM users WHERE `id` = :id";

    public function __construct(
        SessionInterface $session,
        LoggerInterface $logger,
        ConnectionInterface $connection,
    ) {
        $this->session = $session;
        $this->logger = $logger;
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(): array
    {
        $usersData = $this->connection->fetchQuery(self::SELECT_ALL);

        if (count($usersData) === 0) {
            return $usersData; // []
        }

        /** @var User[] */
        $users = array_map(self::class . '::jsonToUser', $usersData);

        return $users;
    }

    /**
     * {@inheritdoc}
     */
    public function findUserOfId(int $id): User
    {
        $userData = $this->connection->fetchPrepare(self::SELECT_BY_ID, [
            ':id' => $id
        ], false);

        if (!isset($userData['id'])) {
            throw new UserNotFoundException();
        }

        return $this->jsonToUser($userData);
    }

    private function jsonToUser(array $userData): User
    {
        return new User(
            $userData['id'],
            $userData['username'],
            $userData['hash'],
        );
    }
}
