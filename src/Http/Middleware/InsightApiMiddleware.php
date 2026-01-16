<?php

declare(strict_types=1);

namespace Rooberthh\InsightApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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

        $this->captureRequest($request, $response->getStatusCode(), $responseTimeMs);

        return $response;
    }

    private function shouldCapture(): bool
    {
        if (! config('insight-api.enabled', true)) {
            return false;
        }

        $samplingRate = config('insight-api.sampling.rate', 1.0);

        if ($samplingRate < 1.0) {
            return (mt_rand() / mt_getrandmax()) <= $samplingRate;
        }

        return true;
    }

    private function captureRequest(Request $request, int $statusCode, float $responseTimeMs): void
    {
        $body = $this->captureRequestBody($request);
        $headers = $this->captureRequestHeaders($request);

        InsightApiRequest::create([
            'method' => $request->method(),
            'route_pattern' => $this->extractRoutePattern($request),
            'uri' => $request->getRequestUri(),
            'headers' => $this->redactionService->redactHeaders($headers),
            'body' => is_array($body) ? $this->redactionService->redactBody($body) : $body,
            'ip_address' => $request->ip() ?? 'unknown',
            'status_code' => $statusCode,
            'response_time_ms' => round($responseTimeMs, 2),
            'captured_at' => now(),
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

        $json = $request->json();

        if ($json !== null && $json->count() > 0) {
            return $json->all();
        }

        if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('PATCH')) {
            return $request->all();
        }

        return null;
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
     * @param Request $request
     */
    private function captureRequestHeaders(Request $request): array
    {
        $headers = [];

        foreach ($request->headers->all() as $key => $values) {
            $headers[$key] = is_array($values) ? implode(', ', $values) : $values;
        }

        return $headers;
    }
}
