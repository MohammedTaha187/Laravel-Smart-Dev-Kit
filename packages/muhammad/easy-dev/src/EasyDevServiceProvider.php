<?php

namespace EasyDev\Laravel;

use Illuminate\Support\ServiceProvider;
use EasyDev\Laravel\Commands\SmartCrudCommand;
use EasyDev\Laravel\Commands\SmartFromMigrationCommand;
use EasyDev\Laravel\Commands\SmartSyncRelationsCommand;

class EasyDevServiceProvider extends ServiceProvider
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
