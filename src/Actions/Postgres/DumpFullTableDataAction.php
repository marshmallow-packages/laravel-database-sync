<?php

namespace Marshmallow\LaravelDatabaseSync\Actions\Postgres;

use Illuminate\Support\Facades\Process;
use Marshmallow\LaravelDatabaseSync\Classes\Config;
use Marshmallow\LaravelDatabaseSync\Console\DatabaseSyncCommand;

class DumpFullTableDataAction
{
    public static function handle(
        string $table,
        Config $config,
        DatabaseSyncCommand $command,
    ): void {
        $dump_flags = config('database-sync.postgres.dump_action_flags');
        $dumpCommand = "pg_dump -h {$config->remote_database_host} -U {$config->remote_database_username} {$dump_flags} -t {$table} {$config->remote_database}";

        $exportCommand = "ssh {$config->remote_user_and_host} \"PGPASSWORD='{$config->remote_database_password}' " . $dumpCommand . " >> {$config->remote_temporary_file}\"";

        if ($command->isDebug()) {
            $command->info(__("Exporting full table :table...", [
                'table' => $table,
            ]));
        }

        $process = Process::timeout($config->process_timeout);
        $process->run($exportCommand)->output();
    }
}
