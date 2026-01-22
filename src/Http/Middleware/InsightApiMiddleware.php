<?php

declare(strict_types=1);

namespace Rooberthh\InsightApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Rooberthh\InsightApi\DataObjects\CreateApiRequest;
use Rooberthh\InsightApi\Jobs\StoreApiRequestJob;
use Symfony\Component\HttpFoundation\Response;

final readonly class InsightApiMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->shouldCapture($request)) {
            return $next($request);
        }

        $startTime = microtime(true);

        $response = $next($request);

        $requestData = CreateApiRequest::fromRequestResponse(
            request: $request,
            response: $response,
            responseTimeMs: (microtime(true) - $startTime) * 1000,
        );

        StoreApiRequestJob::dispatch($requestData);

        return $response;
    }

    private function shouldCapture(Request $request): bool
    {
        if (! $request->expectsJson()) {
            return false;
        }

        $samplingRate = config('insight-api.sampling.rate', 1.0);

        if (! $samplingRate) {
            return false;
        }

        if ($samplingRate < 1.0) {
            return (mt_rand() / mt_getrandmax()) <= $samplingRate;
        }

        return true;
    }
}
