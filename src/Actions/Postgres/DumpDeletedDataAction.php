<?php

namespace Marshmallow\LaravelDatabaseSync\Actions\Postgres;

use Illuminate\Support\Facades\Process;
use Marshmallow\LaravelDatabaseSync\Classes\Config;
use Marshmallow\LaravelDatabaseSync\Console\DatabaseSyncCommand;

class DumpDeletedDataAction
{
    public static function handle(
        string $table,
        bool $deletedAtAvailable,
        Config $config,
        DatabaseSyncCommand $command,
    ): void {
        if (! $deletedAtAvailable) {
            return;
        }

        $dump_flags = config('database-sync.postgres.dump_action_flags');
        $dumpCommand = "pg_dump -h {$config->remote_database_host} -U {$config->remote_database_username} {$dump_flags} --table={$table} {$config->remote_database}";
        
        $exportCommand = "ssh {$config->remote_user_and_host} \"PGPASSWORD='{$config->remote_database_password}' " . $dumpCommand . " >> {$config->remote_temporary_file}\"";

        if ($command->isDebug()) {
            $command->info(__("Exporting deleted records for :table...", [
                'table' => $table,
            ]));
        }

        $process = Process::timeout($config->process_timeout);
        $process->run($exportCommand)->output();
    }
}
