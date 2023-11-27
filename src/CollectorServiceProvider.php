<?php

namespace Collector;

use Collector\Actions\CreateSubscriptions;
use Collector\Concerns\CreateSubscription;
use Collector\Console\InstallCommand;
use Collector\Events\PaymentVerified;
use Collector\Listeners\SubscribeUserToPlan;
use Illuminate\Support\ServiceProvider;

class CollectorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->app->singleton(CreateSubscription::class, CreateSubscriptions::class);

        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'collector');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'collector');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/routes.php');

        $this->registerConfigs();
        $this->registerCommand();
        $this->configureListeners();
    }

    private function registerCommand()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([InstallCommand::class]);
        }
    }

    private function registerConfigs()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/collector.php' => config_path('collector.php'),
            ], 'collector-config');

            // Publishing the views.
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/collector'),
            ], 'collector-views');

            // Publishing assets.
            $this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/collector'),
            ], 'collector-assets');

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/collector'),
            ], 'lang');*/
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/collector.php', 'collector');

        // Register the main class to use with the facade
        $this->app->singleton('collector.manager', CollectorManager::class);
    }

    public function configureListeners()
    {
        $this->app['events']->listen(PaymentVerified::class, SubscribeUserToPlan::class);
    }
}
