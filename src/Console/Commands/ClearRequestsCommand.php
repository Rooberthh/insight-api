<?php

namespace Rooberthh\InsightApi\Console\Commands;

use Illuminate\Console\Command;
use Rooberthh\InsightApi\Actions\ClearRequests;

class ClearRequestsCommand extends Command
{
    protected $signature = 'insight-api:requests:clear';

    protected $description = 'Clears captured api-requests';

    public function handle(ClearRequests $clearRequestsAction): int
    {
        $clearRequestsAction->handle();

        return self::SUCCESS;
    }
}
