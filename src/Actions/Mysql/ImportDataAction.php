<?php

namespace Marshmallow\LaravelDatabaseSync\Actions\Mysql;

use Illuminate\Support\Facades\Process;
use Marshmallow\LaravelDatabaseSync\Classes\Config;
use Marshmallow\LaravelDatabaseSync\Console\DatabaseSyncCommand;

class ImportDataAction
{
    public static function handle(
        Config $config,
        DatabaseSyncCommand $command,
    ): void {
        // Import into local database
        if ($command->isDebug()) {
            $command->info(__('Importing new data into local database...'));
        }

        $importCommand = "mysql -h {$config->local_host} -u {$config->local_database_username} -p'{$config->local_database_password}' $config->local_database < {$config->local_temporary_file}";
        Process::run($importCommand);
    }
}
