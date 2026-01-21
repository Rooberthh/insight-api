<?php

declare(strict_types=1);

namespace Rooberthh\InsightApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Rooberthh\InsightApi\Models\InsightApiPayload;
use Rooberthh\InsightApi\Models\InsightApiRequest;
use Rooberthh\InsightApi\Services\RedactionService;
use Symfony\Component\HttpFoundation\Response;

final class InsightApiMiddleware
{
    public function __construct(
        private readonly RedactionService $redactionService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->shouldCapture()) {
            return $next($request);
        }

        $startTime = microtime(true);

        $response = $next($request);

        $responseTimeMs = (microtime(true) - $startTime) * 1000;

        $this->captureRequest($request, $response, $responseTimeMs);

        return $response;
    }

    private function shouldCapture(): bool
    {
        $samplingRate = config('insight-api.sampling.rate', 1.0);

        if (! $samplingRate) {
            return false;
        }

        if ($samplingRate < 1.0) {
            return (mt_rand() / mt_getrandmax()) <= $samplingRate;
        }

        return true;
    }

    private function captureRequest(Request $request, Response $response, float $responseTimeMs): void
    {
        $insightRequest = InsightApiRequest::query()->create([
            'method' => $request->method(),
            'route_pattern' => $this->extractRoutePattern($request),
            'uri' => $request->getRequestUri(),
            'ip_address' => $request->ip() ?? 'unknown',
            'status_code' => $response->getStatusCode(),
            'response_time_ms' => round($responseTimeMs, 2),
            'captured_at' => now(),
        ]);

        $this->capturePayload($insightRequest, $request, $response);
    }

    private function capturePayload(InsightApiRequest $insightRequest, Request $request, Response $response): void
    {
        $requestBody = $this->captureRequestBody($request);
        $requestHeaders = $this->captureRequestHeaders($request);
        $responseBody = $this->captureResponseBody($response);
        $responseHeaders = $this->captureResponseHeaders($response);

        InsightApiPayload::query()->create([
            'request_id' => $insightRequest->id,
            'request_headers' => $this->redactionService->redactHeaders($requestHeaders),
            'request_body' => is_array($requestBody) ? $this->redactionService->redactBody($requestBody) : $requestBody,
            'response_headers' => $this->redactionService->redactHeaders($responseHeaders),
            'response_body' => is_array($responseBody) ? $this->redactionService->redactBody($responseBody) : $responseBody,
        ]);
    }

    private function extractRoutePattern(Request $request): string
    {
        $route = $request->route();

        if ($route === null) {
            return $request->getRequestUri();
        }

        return '/' . ltrim($route->uri(), '/');
    }

    private function captureRequestBody(Request $request): array|string|null
    {
        if ($this->shouldSkipBody($request)) {
            return null;
        }

        $maxSize = config('insight-api.limits.max_body_size', 64 * 1024);
        $content = $request->getContent();

        if (strlen($content) > $maxSize) {
            return '[BODY_TOO_LARGE]';
        }

        return $request->all();
    }

    private function shouldSkipBody(Request $request): bool
    {
        if (! config('insight-api.limits.capture_file_uploads', false)) {
            if ($request->hasFile('file') || count($request->allFiles()) > 0) {
                return true;
            }
        }

        $contentType = $request->header('Content-Type', '');

        if (! config('insight-api.limits.capture_binary', false)) {
            $binaryTypes = ['application/octet-stream', 'multipart/form-data'];

            foreach ($binaryTypes as $type) {
                if (str_contains($contentType, $type)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return array<string, string>
     */
    private function captureRequestHeaders(Request $request): array
    {
        $headers = [];

        foreach ($request->headers->all() as $key => $values) {
            $headers[$key] = is_array($values) ? implode(', ', $values) : $values;
        }

        return $headers;
    }

    private function captureResponseBody(Response $response): array|string|null
    {
        if (! config('insight-api.capture.response_body', true)) {
            return null;
        }

        if ($this->shouldSkipResponseBody($response)) {
            return null;
        }

        $maxSize = config('insight-api.limits.max_response_size', 64 * 1024);
        $content = $response->getContent();

        if ($content === false || strlen($content) > $maxSize) {
            return '[BODY_TOO_LARGE]';
        }

        $decoded = json_decode($content, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        return null;
    }

    private function shouldSkipResponseBody(Response $response): bool
    {
        $contentType = $response->headers->get('Content-Type', '');

        // Only capture JSON responses
        if (! str_contains($contentType, 'application/json')) {
            return true;
        }

        return false;
    }

    /**
     * @return array<string, string>
     */
    private function captureResponseHeaders(Response $response): array
    {
        $headers = [];

        foreach ($response->headers->all() as $key => $values) {
            $headers[$key] = is_array($values) ? implode(', ', $values) : $values;
        }

        return $headers;
    }
}
