<?php

declare(strict_types=1);

namespace Rooberthh\InsightApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Rooberthh\InsightApi\Actions\RecordRequest;
use Symfony\Component\HttpFoundation\Response;

final readonly class InsightApiMiddleware
{
    public function __construct(
        private RecordRequest $recordRequestAction,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->shouldCapture()) {
            return $next($request);
        }

        $startTime = microtime(true);

        $response = $next($request);

        $responseTimeMs = (microtime(true) - $startTime) * 1000;

        $this->recordRequestAction->handle($request, $response, $responseTimeMs);

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
}
