<?php

namespace Rooberthh\InsightApi\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Rooberthh\InsightApi\Actions\RecordRequest;
use Rooberthh\InsightApi\DataObjects\CreateApiRequest;

final class StoreApiRequestJob implements ShouldQueueAfterCommit
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public CreateApiRequest $request)
    {
        //
    }

    public function handle(RecordRequest $action): void
    {
        $action->handle($this->request);
    }
}
