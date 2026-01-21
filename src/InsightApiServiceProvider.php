<?php

declare(strict_types=1);

namespace Rooberthh\InsightApi;

use Illuminate\Support\ServiceProvider;
use Rooberthh\InsightApi\Console\Commands\ListRequestsCommand;
use Rooberthh\InsightApi\Services\RedactionService;

class InsightApiServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes(
                [
                    __DIR__ . '/../config/insight-api.php' => config_path('insight-api.php'),
                ],
                'insight-api-config',
            );

            $this->publishesMigrations(
                [
                    __DIR__ . '/../database/migrations' => $this->app->databasePath('migrations'),
                ],
                'insight-api-migrations',
            );

            $this->commands(
                [
                    ListRequestsCommand::class,
                ],
            );
        }
    }

    public function register(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->mergeConfigFrom(__DIR__ . '/../config/insight-api.php', 'insight-api');

        $this->app->singleton(RedactionService::class, function () {
            return RedactionService::fromConfig();
        });
    }
}
