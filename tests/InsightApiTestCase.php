<?php

declare(strict_types=1);

namespace Rooberthh\InsightApi\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Rooberthh\InsightApi\InsightApiServiceProvider;

abstract class InsightApiTestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            InsightApiServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('insight-api.sampling.rate', 1.0);
        $app['config']->set('insight-api.storage.path', sys_get_temp_dir() . '/insight-api-test-' . uniqid() . '.sqlite');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate')->run();
    }
}
