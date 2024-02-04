<?php

declare(strict_types=1);

namespace App\Application\Actions;

use App\Domain\DomainException\DomainRecordNotFoundException;
use App\Infrastructure\Database\Connection;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;
use Slim\Views\Twig;
use voku\helper\AntiXSS;

abstract class Action
{
    // Dependencies
    protected SessionInterface $session;
    protected Connection $connection;
    protected Twig $twig;
    protected LoggerInterface $logger;
    protected AntiXSS $antiXss;

    // HTTP
    protected Request $request;
    protected Response $response;
    protected array $args;

    public function __construct(
        SessionInterface $session,
        Connection $connection,
        Twig $twig,
        LoggerInterface $logger,
        AntiXSS $antiXss
    ) {
        $this->session = $session;
        $this->connection = $connection;
        $this->twig = $twig;
        $this->logger = $logger;
        $this->antiXss = $antiXss;
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
     * @return array|object
     */
    protected function getFormData()
    {
        return $this->request->getParsedBody();
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

    /** 
     * Voku AntiXSS string sanitizer
     * IMPORTANT: When calling it, cast the return value to (string) or (array)
     */
    protected function getCleanedRequestBody(
        array|string|null $requestBody = null,
        bool $parsed = true
    ): array|string {
        $requestBody = $requestBody ??
            $parsed ? $this->request->getParsedBody() : (string) $this->request->getBody();

        if ($requestBody === null) {
            return $parsed ? [] : "";
        }

        if (is_string($requestBody) || array_is_list($requestBody)) {
            return $this->antiXss->xss_clean($requestBody);
        }

        $cleanedRequestBody = [];

        foreach ($requestBody as $key => $value) {
            $cleanedKey = $this->antiXss->xss_clean($key);
            $cleanedValue = is_array($value) ?
                $this->getCleanedRequestBody($value) : $this->antiXss->xss_clean($value);
            $cleanedRequestBody[$cleanedKey] = $cleanedValue;
        }

        return $cleanedRequestBody;
    }
}
