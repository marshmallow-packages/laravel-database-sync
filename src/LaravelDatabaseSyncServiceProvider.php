<?php

namespace Marshmallow\LaravelDatabaseSync;

use Illuminate\Support\ServiceProvider;
use Marshmallow\LaravelDatabaseSync\Console\DatabaseSyncCommand;

class LaravelDatabaseSyncServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/database-sync.php' => config_path('database-sync.php'),
        ], 'database-sync-config');

        /*
         * Commands
         */
        if ($this->app->runningInConsole()) {
            $this->commands([
                DatabaseSyncCommand::class,
            ]);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/database-sync.php', 'database-sync');
    }
}
