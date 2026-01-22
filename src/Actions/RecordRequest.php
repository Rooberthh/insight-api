<?php

namespace Rooberthh\InsightApi\Actions;

use Rooberthh\InsightApi\DataObjects\CreateApiRequest;
use Rooberthh\InsightApi\Models\InsightApiRequest;

class RecordRequest
{
    public function handle(CreateApiRequest $data): InsightApiRequest
    {
        $insightRequest = InsightApiRequest::query()->create([
            'request_id' => $data->requestId,
            'method' => $data->method,
            'route_pattern' => $data->routePattern,
            'uri' => $data->uri,
            'ip_address' => $data->ipAddress,
            'status' => $data->status,
            'response_time_ms' => $data->responseTimeMs,
            'captured_at' => now(),
        ]);

        $insightRequest->payload()->create([
            'request_headers' => $data->requestHeaders,
            'request_body' => $data->requestBody,
            'response_headers' => $data->responseHeaders,
            'response_body' => $data->responseBody,
        ]);

        return $insightRequest;
    }
}
