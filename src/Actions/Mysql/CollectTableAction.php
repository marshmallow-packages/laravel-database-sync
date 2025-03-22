<?php

namespace Marshmallow\LaravelDatabaseSync\Actions\Mysql;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Process;
use Marshmallow\LaravelDatabaseSync\Classes\Config;
use Marshmallow\LaravelDatabaseSync\Console\DatabaseSyncCommand;
use Marshmallow\LaravelDatabaseSync\Filters\RejectTables;
use Marshmallow\LaravelDatabaseSync\Filters\RejectTenantTables;
use Marshmallow\LaravelDatabaseSync\Filters\RejectLandlordTables;
use Marshmallow\LaravelDatabaseSync\Filters\FilterSuiteTableOption;
use Marshmallow\LaravelDatabaseSync\Filters\FilterExclusiveTableOption;

class CollectTableAction
{
    public static function handle(
        Config $config,
        DatabaseSyncCommand $command,
    ): Collection {

        $getTablesCommand = "ssh {$config->remote_user_and_host} \"mysql -u {$config->remote_database_username} -p{$config->remote_database_password} -D {$config->remote_database} -N -B -e \\\"SELECT table_name FROM information_schema.columns WHERE table_schema='{$config->remote_database}' AND column_name IN ('created_at', 'updated_at') GROUP BY table_name;\\\"\"";

        $tables = Process::run($getTablesCommand)->output();
        $tables = explode("\n", trim($tables));

        return collect($tables)
            ->reject(fn($table) => RejectTables::apply($table))
            ->filter(fn($table) => FilterExclusiveTableOption::apply($table, $command->option('table')))
            ->filter(fn($table) => FilterSuiteTableOption::apply($table, $command->option('suite')))
            ->reject(fn($table) => RejectLandlordTables::apply($table, $config->multi_tenant_database_type))
            ->reject(fn($table) => RejectTenantTables::apply($table, $config->remote_database, $config->multi_tenant_database_type));
    }
}
