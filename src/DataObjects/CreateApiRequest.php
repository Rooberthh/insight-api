<?php

namespace Rooberthh\InsightApi\DataObjects;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @property array<mixed, mixed> $requestHeaders
 * @property array<mixed, mixed> $requestBody
 * @property array<mixed, mixed> $responseHeaders
 * @property array<mixed, mixed> $responseBody
 */
readonly class CreateApiRequest
{
    public function __construct(
        public int $status,
        public string $method,
        public string $routePattern,
        public string $uri,
        public string $ipAddress,
        public float $responseTimeMs,
        public string $requestId,
        public array $requestHeaders,
        public array $requestBody,
        public array $responseHeaders,
        public array $responseBody,
        public ?Model $requestable,
    ) {
        //
    }

    public static function fromRequestResponse(
        Request $request,
        Response $response,
        float $responseTimeMs,
    ): self {
        $content = $response->getContent();
        $responseBody = ! empty($content) ? json_decode($content, associative: true) : null;

        return new self(
            status: $response->getStatusCode(),
            method: $request->method(),
            routePattern: self::extractRoutePattern($request),
            uri: $request->getRequestUri(),
            ipAddress: $request->ip(),
            responseTimeMs: round($responseTimeMs, 2),
            requestId: $request->fingerprint(),
            requestHeaders: $request->headers->all(),
            requestBody: $request->all(),
            responseHeaders: $response->headers->all(),
            responseBody: $responseBody ?? [],
            requestable: $request->user(),
        );
    }

    protected static function extractRoutePattern(Request $request): string
    {
        return '/' . ltrim($request->route()?->uri() ?? '', '/');
    }
}
