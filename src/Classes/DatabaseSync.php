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
use Marshmallow\LaravelDatabaseSync\Actions\Mysql\DumpCreatedOrUpdatedDataAction;
use Marshmallow\LaravelDatabaseSync\Actions\LogLastSyncDateValueToStorageWithTimestampAction;
use Marshmallow\LaravelDatabaseSync\Actions\LogLastSyncDateForTableWithTimestampAction;
use Marshmallow\LaravelDatabaseSync\Actions\GetLastSyncDateForTableWithFallbackAction;

class DatabaseSync
{
    public function __construct(public Config $config, public DatabaseSyncCommand $command)
    {
        $config->date = GetLastSyncDateAction::handle($config, $command);
        // Capture the sync start time to prevent missing data during sync
        $config->sync_start_time = now();
    }

    public function sync(): self
    {
        $this->command->line(__("Sync :remote_database", ['remote_database' => $this->config->remote_database]));

        // Determine transfer mode
        $use_batch_transfer = $this->shouldUseBatchTransfer();
        
        if ($use_batch_transfer) {
            return $this->syncWithBatchTransfer();
        } else {
            return $this->syncWithIndividualTransfers();
        }
    }

    protected function shouldUseBatchTransfer(): bool
    {
        // If syncing a single table, use individual transfers
        if ($this->command->option('table')) {
            return false;
        }

        // Command option overrides config
        if ($this->command->option('individual-transfers')) {
            return false;
        }

        // Check config setting
        return config('database-sync.file_transfer_mode', 'batch') === 'batch';
    }

    protected function syncWithBatchTransfer(): self
    {
        try {
            // Clear the remote temporary file before starting
            RemoveRemoteFileAction::handle($this->config);
            
            $tables_to_sync = collect();
            $has_data_to_sync = false;

            /**
             * Get the list of tables that contain created_at or updated_at
             */
            $stamped_tables = CollectTableAction::handle($this->config, $this->command);
            if ($stamped_tables->count()) {
                $stamped_tables->each(function ($table) use (&$tables_to_sync, &$has_data_to_sync) {
                    try {
                        $result = $this->command->option('full-sync')
                            ? $this->dumpFullTable($table)
                            : $this->dumpTable($table);
                        
                        if ($result) {
                            $tables_to_sync->push($table);
                            $has_data_to_sync = true;
                        }
                    } catch (OutputWarningException $e) {
                        $this->command->warn($e->getMessage());
                    }
                });
            }

            /** Start dumping the stampless tables, if they are provided in the config. */
            $stampless_tables = CollectStamplessTablesAction::handle($this->config, $this->command);
            if (count($stampless_tables)) {
                $this->command->line(__("We will now start syncing all tables that dont have timestamp columns."));
                $stampless_tables->each(function ($table) use (&$tables_to_sync, &$has_data_to_sync) {
                    $this->dumpFullTable($table);
                    $tables_to_sync->push($table);
                    $has_data_to_sync = true;
                });
            }

            // If we have data to sync, transfer and import it all at once
            if ($has_data_to_sync) {
                $this->command->info(__('Transferring and importing data for :count tables in a single file...', ['count' => $tables_to_sync->count()]));
                CopyRemoteFileToLocalAction::handle($this->config, $this->command);
                ImportDataAction::handle($this->config, $this->command);
                RemoveRemoteFileAction::handle($this->config);
                RemoveLocalFileAction::handle($this->config);

                // Log sync dates for all synced tables
                $tables_to_sync->each(function ($table) {
                    LogLastSyncDateForTableWithTimestampAction::handle($table, $this->config, $this->config->sync_start_time);
                });
            } else {
                $this->command->warn(__('No tables found with data to sync.'));
            }

            // Only update the global sync date if everything succeeded
            LogLastSyncDateValueToStorageWithTimestampAction::handle($this->config, $this->config->sync_start_time);
            $this->command->line(__('Database sync complete! 🚀'));
            $this->command->newLine();
        } catch (\Exception $e) {
            $this->command->error(__('Sync failed: :error', ['error' => $e->getMessage()]));
            $this->command->warn(__('Sync date not updated due to failure. Please resolve the issue and try again.'));
            throw $e;
        }

        return $this;
    }

    protected function syncWithIndividualTransfers(): self
    {
        try {
            /**
             * Get the list of tables that contain created_at or updated_at
             */
            $stamped_tables = CollectTableAction::handle($this->config, $this->command);
            if ($stamped_tables->count()) {
                $this->command->info(__('Using individual file transfers for each table (legacy mode)'));
                $stamped_tables->each(function ($table) {
                    try {
                        $this->command->option('full-sync')
                            ? $this->syncFullTable($table)
                            : $this->syncTable($table);
                    } catch (OutputWarningException $e) {
                        $this->command->warn($e->getMessage());
                    }
                });
            }

            /** Start syncing the stampless tables, if they are provided in the config. */
            $stampless_tables = CollectStamplessTablesAction::handle($this->config, $this->command);
            if (count($stampless_tables)) {
                $this->command->line(__("We will now start syncing all tables that dont have timestamp columns."));
                $stampless_tables->each(fn($table) => $this->syncFullTable($table));
            }

            // Only update the sync date if everything succeeded
            LogLastSyncDateValueToStorageWithTimestampAction::handle($this->config, $this->config->sync_start_time);
            $this->command->line(__('Database sync complete! 🚀'));
            $this->command->newLine();
        } catch (\Exception $e) {
            $this->command->error(__('Sync failed: :error', ['error' => $e->getMessage()]));
            $this->command->warn(__('Sync date not updated due to failure. Please resolve the issue and try again.'));
            throw $e;
        }

        return $this;
    }

    protected function dumpTable(string $table): bool
    {
        // Get table-specific sync date or fallback to global/default
        $table_sync_date = GetLastSyncDateForTableWithFallbackAction::handle($table, $this->config, $this->command);

        // Create a temporary config with the table-specific date for this sync
        $table_config = clone $this->config;
        $table_config->date = $table_sync_date;

        $deleted_at_available = HasDeletedAtColumn::handle($table, $table_config);

        try {
            CountRecordsAction::handle($table, $deleted_at_available, $table_config, $this->command);
        } catch (OutputWarningException $e) {
            // No records to sync for this table
            return false;
        }

        DumpCreatedOrUpdatedDataAction::handle($table, $table_config, $this->command);
        DumpDeletedDataAction::handle($table, $deleted_at_available, $table_config, $this->command);

        if ($this->command->isDebug()) {
            $this->command->newLine();
        }

        return true;
    }

    protected function dumpFullTable(string $table): bool
    {
        $this->command->info(__(":table: syncing all records", [
            'table' => $table,
        ]));

        DumpFullTableDataAction::handle($table, $this->config, $this->command);

        if ($this->command->isDebug()) {
            $this->command->newLine();
        }

        return true;
    }

    // Keep the old methods for backward compatibility and single table syncing
    protected function syncTable(string $table)
    {
        if ($this->dumpTable($table)) {
            CopyRemoteFileToLocalAction::handle($this->config, $this->command);
            ImportDataAction::handle($this->config, $this->command);
            RemoveRemoteFileAction::handle($this->config);
            RemoveLocalFileAction::handle($this->config);

            // Log the sync date for this specific table using the sync start time
            LogLastSyncDateForTableWithTimestampAction::handle($table, $this->config, $this->config->sync_start_time);
        }
    }

    protected function syncFullTable(string $table)
    {
        $this->dumpFullTable($table);
        CopyRemoteFileToLocalAction::handle($this->config, $this->command);
        ImportDataAction::handle($this->config, $this->command);
        RemoveRemoteFileAction::handle($this->config);
        RemoveLocalFileAction::handle($this->config);

        // Log the sync date for this specific table using the sync start time
        LogLastSyncDateForTableWithTimestampAction::handle($table, $this->config, $this->config->sync_start_time);

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
