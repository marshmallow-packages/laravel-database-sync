<?php

namespace Marshmallow\LaravelDatabaseSync\Classes;

use Marshmallow\LaravelDatabaseSync\Console\DatabaseSyncCommand;
use Marshmallow\LaravelDatabaseSync\Actions\GetLastSyncDateAction;
use Marshmallow\LaravelDatabaseSync\Actions\RemoveLocalFileAction;
use Marshmallow\LaravelDatabaseSync\Actions\Mysql\ImportDataAction;
use Marshmallow\LaravelDatabaseSync\Actions\RemoveRemoteFileAction;
use Marshmallow\LaravelDatabaseSync\Actions\Mysql\CollectTableAction;
use Marshmallow\LaravelDatabaseSync\Actions\Mysql\CountRecordsAction;
use Marshmallow\LaravelDatabaseSync\Actions\Mysql\HasDeletedAtColumn;
use Marshmallow\LaravelDatabaseSync\Exceptions\OutputWarningException;
use Marshmallow\LaravelDatabaseSync\Actions\CopyRemoteFileToLocalAction;
use Marshmallow\LaravelDatabaseSync\Actions\Mysql\DumpDeletedDataAction;
use Marshmallow\LaravelDatabaseSync\Actions\Mysql\DumpFullTableDataAction;
use Marshmallow\LaravelDatabaseSync\Actions\Mysql\CollectStamplessTablesAction;
use Marshmallow\LaravelDatabaseSync\Actions\LogLastSyncDateValueToStorageAction;
use Marshmallow\LaravelDatabaseSync\Actions\Mysql\DumpCreatedOrUpdatedDataAction;

class DatabaseSync
{
    public function __construct(public Config $config, public DatabaseSyncCommand $command)
    {
        $config->date = GetLastSyncDateAction::handle($config, $command);
    }

    public function sync(): self
    {
        $this->command->line(__("Sync :remote_database", ['remote_database' => $this->config->remote_database]));

        /**
         * Get the list of tables that contain created_at or updated_at
         */
        $stamped_tables = CollectTableAction::handle($this->config, $this->command);
        if ($stamped_tables->count()) {
            $stamped_tables->each(function ($table) {
                try {
                    $this->syncTable($table);
                } catch (OutputWarningException $e) {
                    $this->command->warn($e->getMessage());
                }
            });
        }

        /** Start syncing the stampless tables, if they are provided in the config. */
        $stampless_tables = CollectStamplessTablesAction::handle($this->config, $this->command);
        if (count($stampless_tables)) {
            $this->command->line(__("We will no start syncing all tables that dont have timestamp columns."));
            $stampless_tables->each(fn($table) => $this->syncFullTable($table));
        }

        LogLastSyncDateValueToStorageAction::handle($this->config);
        $this->command->line(__('Database sync complete! 🚀'));
        $this->command->newLine();

        return $this;
    }

    protected function syncTable(string $table)
    {
        $deleted_at_available = HasDeletedAtColumn::handle($table, $this->config);

        CountRecordsAction::handle($table, $deleted_at_available, $this->config, $this->command);
        DumpCreatedOrUpdatedDataAction::handle($table, $this->config, $this->command);
        DumpDeletedDataAction::handle($table, $deleted_at_available, $this->config, $this->command);
        CopyRemoteFileToLocalAction::handle($this->config, $this->command);
        ImportDataAction::handle($this->config, $this->command);
        RemoveRemoteFileAction::handle($this->config);
        RemoveLocalFileAction::handle($this->config);

        if ($this->command->isDebug()) {
            $this->command->newLine();
        }
    }

    protected function syncFullTable(string $table)
    {
        $this->command->info(__(":table: syncing all records", [
            'table' => $table,
        ]));

        DumpFullTableDataAction::handle($table, $this->config, $this->command);
        CopyRemoteFileToLocalAction::handle($this->config, $this->command);
        ImportDataAction::handle($this->config, $this->command);
        RemoveRemoteFileAction::handle($this->config);
        RemoveLocalFileAction::handle($this->config);

        if ($this->command->isDebug()) {
            $this->command->newLine();
        }
    }

    public function setDatabase(string|array $database_names): self
    {
        [$this->config->remote_database, $this->config->local_database] = self::getDatabaseNames($database_names);
        return $this;
    }

    public function getDatabase(): string
    {
        return $this->config->remote_database;
    }

    public function setMultiTenantDatabaseType(string $multi_tenant_database_type): self
    {
        $this->config->multi_tenant_database_type = $multi_tenant_database_type;
        return $this;
    }

    public function getMultiTenantDatabaseType(): ?string
    {
        return $this->config->multi_tenant_database_type;
    }

    public static function getTenantDatabaseName(string|array $tenant_settings, string|int $tenant_key): string
    {
        return is_array($tenant_settings) ? $tenant_key : $tenant_settings;
    }

    public function isMultiTenantDatabase(): bool
    {
        return !is_null($this->config->multi_tenant_database_type);
    }

    public static function getDatabaseNames(string|array $database_names): array
    {
        return is_string($database_names) ? [$database_names, $database_names] : $database_names;
    }
}
