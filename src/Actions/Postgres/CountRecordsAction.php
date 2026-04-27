<?php

namespace Marshmallow\LaravelDatabaseSync\Actions\Postgres;

use Illuminate\Support\Facades\Process;
use Marshmallow\LaravelDatabaseSync\Classes\Config;
use Marshmallow\LaravelDatabaseSync\Console\DatabaseSyncCommand;
use Marshmallow\LaravelDatabaseSync\Exceptions\OutputWarningException;

class CountRecordsAction
{
    public static function handle(string $table, bool $deletedAtAvailable, Config $config, DatabaseSyncCommand $command): int
    {
        $deletedWhere = $deletedAtAvailable ? ' AND deleted_at IS NOT NULL' : '';
        
        $countCreatedOrUpdatedQuery = "SELECT COUNT(*) FROM {$table} WHERE (created_at >= '{$config->date}' OR updated_at >= '{$config->date}')";
        $countDeletedQuery = $deletedAtAvailable ? "SELECT COUNT(*) FROM {$table} WHERE deleted_at >= '{$config->date}'" : "SELECT 0";

        $countCreatedOrUpdatedCommand = "ssh {$config->remote_user_and_host} \"PGPASSWORD='{$config->remote_database_password}' psql -h {$config->remote_database_host} -U {$config->remote_database_username} -d {$config->remote_database} -t -c \\\"{$countCreatedOrUpdatedQuery}\\\"\"";
        $countDeletedCommand = "ssh {$config->remote_user_and_host} \"PGPASSWORD='{$config->remote_database_password}' psql -h {$config->remote_database_host} -U {$config->remote_database_username} -d {$config->remote_database} -t -c \\\"{$countDeletedQuery}\\\"\"";

        $process = Process::timeout($config->process_timeout);
        
        $createdOrUpdatedResult = $process->run($countCreatedOrUpdatedCommand);
        if ($createdOrUpdatedResult->failed()) {
            throw new \Exception(__('Failed to count created/updated records: :error', ['error' => $createdOrUpdatedResult->errorOutput()]));
        }

        $deletedResult = $process->run($countDeletedCommand);
        if ($deletedResult->failed()) {
            throw new \Exception(__('Failed to count deleted records: :error', ['error' => $deletedResult->errorOutput()]));
        }

        $createdOrUpdatedCount = (int) trim($createdOrUpdatedResult->output());
        $deletedCount = (int) trim($deletedResult->output());
        $totalCount = $createdOrUpdatedCount + $deletedCount;

        if ($totalCount === 0) {
            throw new OutputWarningException(__(':table: No new records found', ['table' => $table]));
        }

        $command->info(__(":table: :count records", [
            'table' => $table,
            'count' => $totalCount,
        ]));

        return $totalCount;
    }
}
