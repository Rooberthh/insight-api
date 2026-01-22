<?php

namespace Rooberthh\InsightApi\Actions;

use Illuminate\Http\Request;
use Rooberthh\InsightApi\Exceptions\CannotRecordRequestException;
use Symfony\Component\HttpFoundation\Response;
use Rooberthh\InsightApi\Models\InsightApiRequest;

class RecordRequest
{
    /**
     * @param  Request   $request
     * @param  Response  $response
     * @param  float     $responseTimeMs
     * @return InsightApiRequest
     * @throws CannotRecordRequestException
     */
    public function handle(Request $request, Response $response, float $responseTimeMs): InsightApiRequest
    {
        if (! $request->expectsJson()) {
            throw new CannotRecordRequestException('[RecordRequest] can only store json-responses.');
        }

        $insightRequest = InsightApiRequest::query()->create(
            [
                'method' => $request->method(),
                'route_pattern' => $this->extractRoutePattern($request),
                'uri' => $request->getRequestUri(),
                'ip_address' => $request->ip() ?? 'unknown',
                'status_code' => $response->getStatusCode(),
                'response_time_ms' => round($responseTimeMs, 2),
                'captured_at' => now(),
            ],
        );

        $insightRequest->payload()->create(
            [
                'request_headers' => $request->headers->all(),
                'request_body' => $request->all(),
                'response_headers' => $response->headers->all(),
                'response_body' => json_decode($response->getContent(), associative: true),
            ],
        );

        return $insightRequest;
    }

    protected function extractRoutePattern(Request $request): string
    {
        return '/' . ltrim($request->route()->uri(), '/');
    }
}
