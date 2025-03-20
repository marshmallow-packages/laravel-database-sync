<?php

namespace Marshmallow\LaravelDatabaseSync\Actions\Mysql;

use Illuminate\Support\Facades\Process;
use Marshmallow\LaravelDatabaseSync\Classes\Config;
use Marshmallow\LaravelDatabaseSync\Console\DatabaseSyncCommand;

class DumpDataAction
{
    public static function handle(
        string $table,
        Config $config,
        DatabaseSyncCommand $command,
    ): void {
        $dumpCommand = "mysqldump -u {$config->remote_database_username} -p{$config->remote_database_password} --skip-lock-tables --no-create-info --complete-insert --skip-triggers --replace --where='id IN (SELECT id FROM {$table} WHERE created_at >= \\\"{$config->date} 00:00:00\\\" OR updated_at >= \\\"{$config->date} 00:00:00\\\")' {$config->remote_database} {$table}";

        /**
         * Run all dump commands and save to a new .sql file
         */
        $exportCommand = "ssh {$config->remote_host} \"" . $dumpCommand . " >> {$config->remote_temporary_file}\"";
        if ($command->isDebug()) {
            $command->info(__("Exporting new or updated records for :table...", [
                'table' => $table,
            ]));
        }

        Process::run($exportCommand)->output();
    }
}
