<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\User;

use App\Infrastructure\Database\DatabaseConnectionException;
use App\Infrastructure\Database\DatabaseConnectionInterface;
use App\Domain\User\User;
use App\Domain\User\UserNotFoundException;
use App\Domain\User\UserRepository;
use Psr\Log\LoggerInterface;

class UserRepositoryImplementation implements UserRepository
{
    private const SELECT_ALL = "SELECT `id`, `username`, `hash` FROM users";
    private const SELECT_BY_ID = "SELECT `id`, `username`, `hash` FROM users WHERE `id` = :id";

    public function __construct(
        private LoggerInterface $logger,
        private DatabaseConnectionInterface $connection,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(): array
    {
        $usersData = $this->connection->fetch(self::SELECT_ALL);

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
        try {
            $userData = $this->connection->fetch(self::SELECT_BY_ID, [
                ':id' => $id
            ], false);

            if (!isset($userData['id'])) {
                throw new DatabaseConnectionException;
            }
        } catch (DatabaseConnectionException $e) {
            throw new UserNotFoundException("User of id $id was not found");
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
