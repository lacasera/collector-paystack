<?php

namespace Collector\Tests;

use Collector\CollectorManager;
use Collector\CollectorServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // A single call configures both collectable resolution and the
        // Subscription→owner relationship.
        CollectorManager::useCustomerModel(TestUser::class);
    }

    protected function getPackageProviders($app): array
    {
        return [
            CollectorServiceProvider::class,
        ];
    }

    /**
     * Run the base Laravel migrations first (so the `users` table exists) and
     * then the package migrations that decorate it.
     *
     * The package migrations are applied with `artisan migrate` rather than
     * `loadMigrationsFrom()` so Testbench does not attempt to roll them back on
     * teardown — their `down()` cannot drop the indexed `paystack_id` column on
     * SQLite. Each test uses a fresh in-memory database, so no rollback is
     * needed anyway.
     */
    protected function defineDatabaseMigrations(): void
    {
        $this->loadLaravelMigrations();

        $this->artisan('migrate', [
            '--path' => realpath(__DIR__ . '/../database/migrations'),
            '--realpath' => true,
        ]);
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Required by the `web` middleware group (session/cookie encryption) used
        // by the billing routes.
        config()->set('app.key', 'base64:' . base64_encode(random_bytes(32)));

        config()->set('collector.secret', 'test_secret_key');
        config()->set('collector.currency', 'NGN');
        config()->set('collector.collectables.user.model', TestUser::class);
    }
}
