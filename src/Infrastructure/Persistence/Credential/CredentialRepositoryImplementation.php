<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Credential;

use App\Domain\Credential\Credential;
use App\Domain\Credential\CredentialNotFoundException;
use App\Domain\Credential\CredentialRepository;
use App\Domain\Credential\InvalidCredentialException;
use App\Domain\User\User;
use App\Infrastructure\Database\Connection;
use App\Infrastructure\Database\ConnectionInterface;
use Odan\Session\PhpSession;
use Odan\Session\SessionInterface;
use Psr\Log\LoggerInterface;

class CredentialRepositoryImplementation implements CredentialRepository
{
    private PhpSession $session;
    private LoggerInterface $logger;
    private Connection $connection;

    private const SELECT_BY_USERNAME = "SELECT `id`, `username`, `hash` FROM users WHERE `username` = :username";

    public function __construct(
        SessionInterface $session,
        LoggerInterface $logger,
        ConnectionInterface $connection,
    ) {
        $this->session = $session;
        $this->logger = $logger;
        $this->connection = $connection;
    }

    public function authenticate(string $username, string $password): Credential
    {
        if ($this->session->has('user')) {
            return $this->session->get('user');
        }

        $this->validate($username, $password);

        $userData = $this->connection->fetchPrepare(self::SELECT_BY_USERNAME, [
            ":username" => $username
        ], false);

        if (!isset($userData['id'])) {
            $this->logger->error("Authentication attempt failed for username $username");
            throw new CredentialNotFoundException();
        }

        $user = new User(
            $userData["id"],
            $userData["username"],
            $userData["hash"],
        );

        if (!password_verify($password, $user->getHash())) {
            $this->logger->error("Authentication attempt failed for username $username");
            throw new CredentialNotFoundException();
        }

        $credential = new Credential($user);

        $this->session->set('user', $credential);
        $this->logger->info("User $username successfuly authenticated");

        return $credential;
    }

    private function validate(string ...$credentials): void
    {
        if (!isset($credentials) || count($credentials) === 0) {
            throw new InvalidCredentialException();
        }

        foreach ($credentials as $credential) {
            if (empty($credential)) {
                throw new InvalidCredentialException();
            }

            // ...
            // TODO: REGEX
            // ...
        }
    }

    public function destroy(): void
    {
        /** @var ?Credential */
        $credential = $this->session->get("user");
        $username = $credential === null ? '-' : $credential->getUsername();
        
        $this->session->destroy();
        $this->logger->info("User $username successfully logged out");
    }
}
