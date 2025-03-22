<?php

namespace Marshmallow\LaravelDatabaseSync\Actions\Mysql;

use Illuminate\Support\Facades\Process;
use Marshmallow\LaravelDatabaseSync\Classes\Config;
use Marshmallow\LaravelDatabaseSync\Console\DatabaseSyncCommand;

class DumpDeletedDataAction
{
    public static function handle(
        string $table,
        bool $deleted_at_available,
        Config $config,
        DatabaseSyncCommand $command,
    ): void {
        if (!$deleted_at_available) {
            return;
        }

        $dump_flags = config('database-sync.mysql.dump_action_flags');
        $dumpCommand = "mysqldump -u {$config->remote_database_username} -p{$config->remote_database_password} {$dump_flags} --where='id IN (SELECT id FROM {$table} WHERE deleted_at >= \\\"{$config->date} 00:00:00\\\")' {$config->remote_database} {$table}";

        /**
         * Run all dump commands and save to a new .sql file
         */
        $exportCommand = "ssh {$config->remote_user_and_host} \"" . $dumpCommand . " >> {$config->remote_temporary_file}\"";
        if ($command->isDebug()) {
            $command->info(__("Exporting deleted records for :table...", [
                'table' => $table,
            ]));
        }

        Process::run($exportCommand)->output();
    }
}
