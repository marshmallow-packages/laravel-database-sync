<?php

namespace Marshmallow\LaravelDatabaseSync\Actions\Postgres;

use Illuminate\Support\Facades\Process;
use Marshmallow\LaravelDatabaseSync\Classes\Config;
use Marshmallow\LaravelDatabaseSync\Console\DatabaseSyncCommand;

class DumpCreatedOrUpdatedDataAction
{
    public static function handle(
        string $table,
        Config $config,
        DatabaseSyncCommand $command,
    ): void {
        $dump_flags = config('database-sync.postgres.dump_action_flags');
        $whereClause = "created_at >= '{$config->date}' OR updated_at >= '{$config->date}'";
        
        // Use pg_dump with a custom query approach
        $dump_flags = config('database-sync.postgres.dump_action_flags');
        
        // Create a temporary query to get only the needed records
        $queryCommand = "SELECT * FROM {$table} WHERE {$whereClause}";
        $dumpCommand = "pg_dump -h {$config->remote_database_host} -U {$config->remote_database_username} {$dump_flags} --table={$table} {$config->remote_database}";
        
        $exportCommand = "ssh {$config->remote_user_and_host} \"PGPASSWORD='{$config->remote_database_password}' " . $dumpCommand . " >> {$config->remote_temporary_file}\"";
        
        if ($command->isDebug()) {
            $command->info(__("Exporting new or updated records for :table...", [
                'table' => $table,
            ]));
        }

        $process = Process::timeout($config->process_timeout);
        $process->run($exportCommand)->output();
    }
}
