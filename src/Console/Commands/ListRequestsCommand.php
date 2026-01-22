<?php

declare(strict_types=1);

namespace Rooberthh\InsightApi\Console\Commands;

use Illuminate\Console\Command;
use Rooberthh\InsightApi\Models\InsightApiRequest;

final class ListRequestsCommand extends Command
{
    protected $signature = 'insight-api:requests
        {--limit=50 : Maximum number of requests to show}';

    protected $description = 'List captured API requests';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $endpoint = $this->option('endpoint');

        $requests = InsightApiRequest::query()->orderByDesc('captured_at')->limit($limit)->get();

        if ($requests->isEmpty()) {
            $this->info('No requests captured yet.');
            $this->newLine();
            $this->line('Attach the middleware to routes you want to capture:');
            $this->line('  Route::middleware(InsightApiMiddleware::class)->group(...)');

            return self::SUCCESS;
        }

        $this->info("Showing {$requests->count()} captured request(s):");
        $this->newLine();

        $this->table(
            ['Method', 'Route Pattern', 'IP', 'Status', 'Time (ms)', 'Captured At'],
            $requests->map(fn($request) => [
                $request->method,
                $this->truncate($request->route_pattern, 40),
                $request->ip_address,
                $this->colorStatus($request->status_code),
                number_format($request->response_time_ms, 2),
                $request->captured_at->format('Y-m-d H:i:s'),
            ])->toArray(),
        );

        $totalCount = InsightApiRequest::query()->count();

        if ($totalCount > $requests->count()) {
            $this->newLine();
            $this->line("Showing {$requests->count()} of {$totalCount} total requests. Use --limit to see more.");
        }

        return self::SUCCESS;
    }

    private function truncate(string $value, int $length): string
    {
        if (strlen($value) <= $length) {
            return $value;
        }

        return substr($value, 0, $length - 3) . '...';
    }

    private function colorStatus(int $status): string
    {
        return match (true) {
            $status >= 500 => "<fg=red>{$status}</>",
            $status >= 400 => "<fg=yellow>{$status}</>",
            $status >= 300 => "<fg=cyan>{$status}</>",
            $status >= 200 => "<fg=green>{$status}</>",
            default => (string) $status,
        };
    }
}
