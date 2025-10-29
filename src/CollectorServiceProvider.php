<?php

namespace Collector;

use Collector\Actions\CreateSubscriptions;
use Collector\Concerns\CreateSubscription;
use Collector\Console\InstallCommand;
use Collector\Events\PaymentVerified;
use Collector\Listeners\SubscribeUserToPlan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class CollectorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->app->singleton(CreateSubscription::class, CreateSubscriptions::class);

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'collector');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/../routes/routes.php');

        $this->registerPublishables();
        $this->registerCommands();
        $this->configureEventListeners();
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/collector.php', 'collector');
        $this->app->singleton('collector.manager', CollectorManager::class);
    }

    /**
     * Register publishable resources.
     */
    private function registerPublishables(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/collector.php' => config_path('collector.php'),
        ], 'collector-config');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/collector'),
        ], 'collector-views');

        $this->publishes([
            __DIR__ . '/../public' => public_path('vendor/collector'),
        ], 'collector-assets');
    }

    /**
     * Register package commands.
     */
    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);
        }
    }

    /**
     * Configure event listeners.
     */
    private function configureEventListeners(): void
    {
        Event::listen(PaymentVerified::class, SubscribeUserToPlan::class);
    }
}
