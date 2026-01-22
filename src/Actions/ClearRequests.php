<?php

namespace Rooberthh\InsightApi\Actions;

use Rooberthh\InsightApi\Models\InsightApiPayload;
use Rooberthh\InsightApi\Models\InsightApiRequest;

class ClearRequests
{
    public function handle(): void
    {
        InsightApiPayload::query()->delete();
        InsightApiRequest::query()->delete();
    }
}
