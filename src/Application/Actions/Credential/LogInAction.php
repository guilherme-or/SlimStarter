<?php

declare(strict_types=1);

namespace App\Application\Actions\Credential;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class LogInAction extends CredentialAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $requestBody = (array) $this->getCleanedRequestBody();

        if (!isset($requestBody['username'], $requestBody['password'])) {
            throw new HttpBadRequestException(
                $this->request,
                "Request body must contain 'username' and 'password' keys"
            );
        }

        $credential = $this->credentialRepository->authenticate(
            trim((string) $requestBody['username']),
            trim((string) $requestBody['password'])
        );

        return $this->respondWithData($credential);
    }
}
