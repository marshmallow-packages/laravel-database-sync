<?php

namespace Marshmallow\LaravelDatabaseSync\Actions\Mysql;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Process;
use Marshmallow\LaravelDatabaseSync\Classes\Config;
use Marshmallow\LaravelDatabaseSync\Console\DatabaseSyncCommand;
use Marshmallow\LaravelDatabaseSync\Actions\ApplyTableFiltersAction;

class CollectTableAction
{
    public static function handle(
        Config $config,
        DatabaseSyncCommand $command,
    ): Collection {

        $command->line(__("Fetching tables with created_at or updated_at columns after :date...", [
            'date' => $config->date->format('Y-m-d'),
        ]));

        $getTablesCommand = "ssh {$config->remote_user_and_host} \"mysql -u {$config->remote_database_username} -p{$config->remote_database_password} -D {$config->remote_database} -N -B -e \\\"SELECT table_name FROM information_schema.columns WHERE table_schema='{$config->remote_database}' AND column_name IN ('created_at', 'updated_at') GROUP BY table_name;\\\"\"";

        $tables = Process::run($getTablesCommand)->output();
        $tables = explode("\n", trim($tables));

        return ApplyTableFiltersAction::handle(
            $tables,
            $config,
            $command,
        );
    }
}
