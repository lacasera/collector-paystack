<?php

namespace Collector\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class InstallCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'collector:install';

    protected $description = 'Install and initializes all collector resources';

    public function handle(): void
    {
        $this->callSilent('vendor:publish', ['--tag' => 'collector-provider']);
        $this->callSilent('vendor:publish', ['--tag' => 'collector-config']);
        $this->callSilent('vendor:publish', ['--tag' => 'collector-views']);

        $this->registerCollectorServiceProvider();

        $this->info('Collector scaffolding installed successfully...');
    }

    private function registerCollectorServiceProvider()
    {
        $namespace = Str::replaceLast('\\', '', $this->laravel->getNamespace());

        $appConfig = file_get_contents(config_path('app.php'));

        if (Str::contains($appConfig, $namespace.'\\Providers\\CollectorServiceProvider::class')) {
            return;
        }

        file_put_contents(config_path('app.php'), str_replace(
            "{$namespace}\\Providers\EventServiceProvider::class,",
            "{$namespace}\\Providers\EventServiceProvider::class,".PHP_EOL."        {$namespace}\Providers\CollectorServiceProvider::class,",
            $appConfig
        ));

        file_put_contents(app_path('Providers/CollectorServiceProvider.php'), str_replace(
            "namespace App\Providers;",
            "namespace {$namespace}\Providers;",
            file_get_contents(__DIR__.'/stubs/CollectorServiceProvider.php')
        ));
    }
}
