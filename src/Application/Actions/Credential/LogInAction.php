<?php

declare(strict_types=1);

namespace App\Application\Actions\Credential;

use Psr\Http\Message\ResponseInterface as Response;

class LogInAction extends CredentialAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $body = $this->getBodyData(['username', 'password']);

        $token = $this->credentialRepository->authenticate(
            trim((string) $body['username']),
            trim((string) $body['password'])
        );

        return $this->respondWithData($token);
    }
}
