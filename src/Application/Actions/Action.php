<?php

declare(strict_types=1);

namespace App\Application\Actions;

use App\Adapter\Database\DatabaseConnectionInterface;
use App\Domain\DomainException\DomainRecordInvalidationException;
use App\Domain\DomainException\DomainRecordNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;
use Slim\Views\Twig;
use voku\helper\AntiXSS;

abstract class Action
{
    protected Request $request;
    protected Response $response;
    protected array $args;

    public function __construct(
        protected DatabaseConnectionInterface $connection,
        protected Twig $twig,
        protected LoggerInterface $logger,
        protected AntiXSS $antiXss
    ) {
    }

    /**
     * @throws HttpNotFoundException
     * @throws HttpBadRequestException
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;

        try {
            return $this->action();
        } catch (DomainRecordInvalidationException $e) {
            throw new HttpBadRequestException($this->request, $e->getMessage());
        } catch (DomainRecordNotFoundException $e) {
            throw new HttpNotFoundException($this->request, $e->getMessage());
        }
    }

    /**
     * @throws DomainRecordNotFoundException
     * @throws HttpBadRequestException
     */
    abstract protected function action(): Response;

    /**
     * @param string[] $requiredFields
     * @return array|object|null
     */
    protected function getBodyData(array $requiredFields = []): array|object|null
    {
        $body = $this->request->getParsedBody();

        if ($body === null || is_object($body) || empty($requiredFields)) {
            return $body;
        }

        $missingFields = array_filter($requiredFields, function ($field) use ($body) {
            return !isset ($body[$field]);
        });

        if (count($missingFields) > 0) {
            throw new HttpBadRequestException(
                $this->request,
                "Missing fields in request body: " . implode(', ', $missingFields)
            );
        }

        return $body;
    }

    /**
     * @return mixed
     * @throws HttpBadRequestException
     */
    protected function resolveArg(string $name)
    {
        if (!isset($this->args[$name])) {
            throw new HttpBadRequestException($this->request, "Could not resolve argument `{$name}`.");
        }

        return $this->args[$name];
    }

    /**
     * @param array|object|null $data
     */
    protected function respondWithData($data = null, int $statusCode = 200): Response
    {
        $payload = new ActionPayload($statusCode, $data);

        return $this->respond($payload);
    }

    protected function respond(ActionPayload $payload): Response
    {
        $json = json_encode($payload, JSON_PRETTY_PRINT);
        $this->response->getBody()->write($json);

        return $this->response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($payload->getStatusCode());
    }

    /**
     * Twig template renderer
     * PAgesPath: "src/Application/Views/Pages"
     */
    protected function renderTemplate(
        string $template,
        array|object $data = []
    ): Response {
        $response = $this->response->withHeader('Content-Type', 'text/html; charset=utf-8');
        $templatePath = "Pages/" . $template;

        return $this->twig->render($response, $templatePath, $data);
    }


}
