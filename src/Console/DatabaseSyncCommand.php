<?php

namespace Marshmallow\LaravelDatabaseSync\Console;

use Illuminate\Support\Arr;
use Illuminate\Console\Command;
use Marshmallow\LaravelDatabaseSync\Classes\Config;
use Symfony\Component\Console\Output\OutputInterface;
use Marshmallow\LaravelDatabaseSync\Classes\DatabaseSync;
use Marshmallow\LaravelDatabaseSync\Filters\FilterTenantOption;

class DatabaseSyncCommand extends Command
{
    protected $signature = 'db-sync {--date=} {--suite=} {--table=} {--tenant=} {--skip-landlord}';

    protected $description = 'Sync new and updated records from Laravel Forge to local';

    public function handle()
    {
        $config = $this->buildConfig();
        $database_sync = new DatabaseSync($config, $this);

        if (config('database-sync.multi_tenant')) {
            $this->syncMultiTenantDatabases($database_sync);
        } else {
            $database_sync->sync();
        }
    }

    protected function buildConfig(): Config
    {
        return Config::make(
            /** Remote host settings */
            remote_user_and_host: config('database-sync.remote_user_and_host'),
            remote_database: config('database-sync.remote_database'),
            remote_database_username: config('database-sync.remote_database_username'),
            remote_database_password: config('database-sync.remote_database_password'),

            /** Local host settings */
            local_host: config('database-sync.local_host'),
            local_database: config('database-sync.local_database'),
            local_database_username: config('database-sync.local_database_username'),
            local_database_password: config('database-sync.local_database_password'),
        );
    }

    protected function syncMultiTenantDatabases(DatabaseSync $database_sync)
    {
        $settings = config('database-sync.multi_tenant');
        $database_sync = $database_sync
            ->setDatabase(Arr::get($settings, 'landlord.database_name'))
            ->setMultiTenantDatabaseType('landlord');

        if (!$this->option('skip-landlord')) {
            $this->alert(__('We are going to sync multiple databases. We will start with the landlord database.'));
            $database_sync->sync();
        }

        $this->alert(__('Next we sync the tenant databases'));

        $tenant_databases = Arr::get($settings, 'tenants.database_names', []);
        collect($tenant_databases)
            ->filter(fn($tenant_settings, $tenant_key) => FilterTenantOption::apply($tenant_settings, $tenant_key, $this->option('tenant')))
            ->each(function ($tenant_settings, $tenant_key) use ($database_sync) {
                $database_name = DatabaseSync::getTenantDatabaseName($tenant_settings, $tenant_key);
                $this->info(__('Syncing the tenant database first: :tenant', ['tenant' => $database_name]));
                $database_sync
                    ->setMultiTenantDatabaseType('tenant')
                    ->setDatabase($database_name)
                    ->sync();
            });
    }

    public function isDebug(): bool
    {
        return $this->getOutput()->getVerbosity() == OutputInterface::VERBOSITY_DEBUG;
    }
}
