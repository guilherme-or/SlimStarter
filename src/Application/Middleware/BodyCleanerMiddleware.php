<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Factory\StreamFactory;
use voku\helper\AntiXSS;

class BodyCleanerMiddleware implements Middleware
{
    private const BODY_CLEANER_METHODS = ['POST', 'PUT', 'PATCH'];

    public function __construct(
        private AntiXSS $antiXss
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        if (!in_array($request->getMethod(), self::BODY_CLEANER_METHODS, true)) {
            return $handler->handle($request);
        }

        $body = $request->getBody()->getContents();
        $parsedBody = $request->getParsedBody();

        $cleanBody = $this->cleanRequestBody($body);
        $cleanParsedBody = $this->cleanRequestParsedBody($parsedBody);

        return $handler->handle($request
            ->withBody($cleanBody)
            ->withParsedBody($cleanParsedBody));
    }

    private function cleanRequestBody(string $b): StreamInterface
    {
        $cleanBody = !empty($b) ? $this->antiXss->xss_clean($b) : "";
        return (new StreamFactory())->createStream($cleanBody);
    }

    private function cleanRequestParsedBody(array|object|null $pb): array|object|null
    {
        if ($pb === null || is_object($pb)) {
            return $pb;
        }

        return array_map(function ($value) {
            return is_string($value)
                ? $this->antiXss->xss_clean($value)
                : (
                    is_array($value)
                    ? $this->cleanRequestParsedBody($value)
                    : $value
                );
        }, $pb);
    }
}
