<?php

namespace Collector;

use App\Models\User;
use Collector\Actions\CreateSubscriptions;
use Collector\Concerns\CreateSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class CollectorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->app->singleton(CreateSubscription::class, CreateSubscriptions::class);
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'collector');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'collector');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/routes.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'config/collector.php' => config_path('collector.php'),
            ], 'config');

            // Publishing the views.
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/collector'),
            ], 'views');

            // Publishing assets.
            $this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/collector'),
            ], 'assets');


            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/collector'),
            ], 'lang');*/

            // Registering package commands.
            // $this->commands([]);

          //  Collector::billable(User::class)->checkPlanEligibility(function (User $billable, Plan $plan) {
                // if ($billable->projects > 5 && $plan->name == 'Basic') {
                //     throw ValidationException::withMessages([
                //         'plan' => 'You have too many projects for the selected plan.'
                //     ]);
                // }
            //});
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
}
