<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Credential;

use App\Infrastructure\Database\DatabaseConnectionException;
use App\Infrastructure\Database\DatabaseConnectionInterface;
use App\Domain\Credential\CredentialNotFoundException;
use App\Domain\Credential\CredentialPayload;
use App\Domain\Credential\CredentialRepository;
use App\Domain\Credential\CredentialInvalidationException;
use App\Domain\Credential\CredentialToken;
use App\Domain\User\User;
use Firebase\JWT\JWT;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;

class CredentialRepositoryImplementation implements CredentialRepository
{
    private const SELECT_BY_USERNAME = "SELECT `id`, `username`, `hash` FROM users WHERE `username` = :username";

    public function __construct(
        private LoggerInterface $logger,
        private ClientInterface $httpClient,
        private DatabaseConnectionInterface $connection,
    ) {
    }

    /** @inheritDoc */
    public function authenticate(string $username, string $password): CredentialToken
    {
        $this->validate($username, $password);
        $userData = [];

        try {
            $userData = $this->connection->fetch(self::SELECT_BY_USERNAME, [
                ":username" => $username
            ], false);

            if (!isset($userData['id'])) {
                throw new DatabaseConnectionException();
            }
        } catch (DatabaseConnectionException $e) {
            $this->logger->error("Username authentication failed for $username");
            throw new CredentialNotFoundException();
        }

        $user = new User(
            $userData["id"],
            $userData["username"],
            $userData["hash"],
        );

        if (!password_verify($password, $user->getHash())) {
            $this->logger->error("Password authentication failed for $username");
            throw new CredentialNotFoundException();
        }

        $payload = new CredentialPayload($user);
        $token = new CredentialToken(
            JWT::encode(
                $payload->jsonSerialize(),
                $_ENV["JWT_SECRET"],
                "HS256"
            ),
            $payload
        );

        setcookie(CredentialToken::TOKEN_NAME, $token->getToken(), [
            "expires" => $token->getPayload()->getExpirationTime()->getTimestamp(),
            "httponly" => true,
            "samesite" => "Strict",
        ]);

        $this->logger->info(
            "User $username successfully authenticated at "
            . $payload->getIssuedTime()->format("Y-m-d H:i:s")
        );

        return $token;
    }

    // /** @inheritDoc */
    // public function authenticate(string $username, string $password): CredentialToken
    // {
    //     $this->validate($username, $password);
    //     $userData = [];

    //     try {
    //         $userData = $this->connection->fetch(self::SELECT_BY_USERNAME, [
    //             ":username" => $username
    //         ], false);

    //         if (!isset($userData['id'])) {
    //             throw new DatabaseConnectionException();
    //         }
    //     } catch (DatabaseConnectionException $e) {
    //         $this->logger->error("Username authentication failed for $username");
    //         throw new CredentialNotFoundException();
    //     }

    //     $user = new User(
    //         $userData["id"],
    //         $userData["username"],
    //         $userData["hash"],
    //     );

    //     if (!password_verify($password, $user->getHash())) {
    //         $this->logger->error("Password authentication failed for $username");
    //         throw new CredentialNotFoundException();
    //     }

    //     $payload = new CredentialPayload($user);
    //     $token = new CredentialToken(
    //         JWT::encode(
    //             $payload->jsonSerialize(),
    //             $_ENV["JWT_SECRET"],
    //             "HS256"
    //         ),
    //         $payload
    //     );

    //     setcookie(CredentialToken::TOKEN_NAME, $token->getToken(), [
    //         "expires" => $token->getPayload()->getExpirationTime()->getTimestamp(),
    //         "httponly" => true,
    //         "samesite" => "Strict",
    //     ]);

    //     $this->logger->info(
    //         "User $username successfully authenticated at "
    //         . $payload->getIssuedTime()->format("Y-m-d H:i:s")
    //     );

    //     return $token;
    // }

    public function destroy(): void
    {
        unset($_COOKIE[CredentialToken::TOKEN_NAME]);
        setcookie(CredentialToken::TOKEN_NAME, '', -1);
    }

    private function validate(string ...$credentials): void
    {
        if (!isset($credentials) || count($credentials) === 0) {
            throw new CredentialInvalidationException();
        }

        foreach ($credentials as $credential) {
            if (empty($credential)) {
                throw new CredentialInvalidationException();
            }

            // ...
            // Check regex
            // ...
        }
    }
}
