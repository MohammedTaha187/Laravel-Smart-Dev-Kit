<?php

namespace Muhammad\StarterKit;

use Illuminate\Support\ServiceProvider;
use Muhammad\StarterKit\Commands\SmartCrudCommand;
use Muhammad\StarterKit\Commands\SmartFromMigrationCommand;
use Muhammad\StarterKit\Commands\SmartSyncRelationsCommand;

class StarterKitServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/starter-kit.php', 'starter-kit'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/starter-kit.php' => config_path('starter-kit.php'),
            ], 'starter-kit-config');

            $this->publishes([
                __DIR__.'/../stubs' => base_path('stubs/starter-kit'),
            ], 'starter-kit-stubs');

            $this->commands([
                SmartCrudCommand::class,
                SmartSyncRelationsCommand::class,
                SmartFromMigrationCommand::class,
            ]);
        }
    }
}
