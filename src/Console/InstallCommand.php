<?php

namespace Collector\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'collector:install {--force : Overwrite existing files}';

    protected $description = 'Install and initialize all Collector PayStack resources';

    public function handle(): int
    {
        $this->info('Installing Collector PayStack...');

        // Publish configuration
        $this->call('vendor:publish', [
            '--tag' => 'collector-config',
            '--force' => $this->option('force'),
        ]);

        // Publish assets
        $this->call('vendor:publish', [
            '--tag' => 'collector-assets',
            '--force' => $this->option('force'),
        ]);

        // Publish views if needed
        if ($this->confirm('Do you want to publish the views for customization?', false)) {
            $this->call('vendor:publish', [
                '--tag' => 'collector-views',
                '--force' => $this->option('force'),
            ]);
        }

        // Run migrations
        if ($this->confirm('Do you want to run the migrations now?', true)) {
            $this->call('migrate');
        }

        $this->displayPostInstallationInstructions();

        $this->info('Collector PayStack installed successfully!');

        return Command::SUCCESS;
    }

    private function displayPostInstallationInstructions(): void
    {
        $this->newLine();
        $this->info('📋 Next steps:');
        $this->line('1. Add your PayStack credentials to your .env file:');
        $this->line('   PAYSTACK_SECRET_KEY=your_secret_key');
        $this->line('   COLLECTOR_CURRENCY=NGN');
        $this->newLine();
        $this->line('2. Add the Collectable trait to your User model:');
        $this->line('   use Collector\Collectable;');
        $this->newLine();
        $this->line('3. Configure your subscription plans in config/collector.php');
        $this->newLine();
        $this->line('4. Set up your PayStack webhook URL in your PayStack dashboard:');
        $this->line('   ' . url('/collector/webhooks'));
        $this->newLine();
        $this->line('5. Visit the billing portal at: ' . url('/collector/billing'));
    }
}
