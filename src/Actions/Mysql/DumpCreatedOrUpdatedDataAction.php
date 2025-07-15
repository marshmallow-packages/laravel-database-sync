<?php

namespace Marshmallow\LaravelDatabaseSync\Actions\Mysql;

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
        $dump_flags = config('database-sync.mysql.dump_action_flags');
        $dumpCommand = "mysqldump -u {$config->remote_database_username} -p{$config->remote_database_password} {$dump_flags} --where='id IN (SELECT id FROM {$table} WHERE created_at >= \\\"{$config->date}\\\" OR updated_at >= \\\"{$config->date}\\\")' {$config->remote_database} {$table}";

        /**
         * Run all dump commands and save to a new .sql file
         */
        $exportCommand = "ssh {$config->remote_user_and_host} \"" . $dumpCommand . " >> {$config->remote_temporary_file}\"";
        if ($command->isDebug()) {
            $command->info(__("Exporting new or updated records for :table...", [
                'table' => $table,
            ]));
        }

        $process = Process::timeout($config->process_timeout);
        $process->run($exportCommand)->output();
    }
}
