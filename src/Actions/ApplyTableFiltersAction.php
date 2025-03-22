<?php

namespace Marshmallow\LaravelDatabaseSync\Actions;

use Illuminate\Support\Collection;
use Marshmallow\LaravelDatabaseSync\Classes\Config;
use Marshmallow\LaravelDatabaseSync\Filters\RejectTables;
use Marshmallow\LaravelDatabaseSync\Filters\RejectTenantTables;
use Marshmallow\LaravelDatabaseSync\Console\DatabaseSyncCommand;
use Marshmallow\LaravelDatabaseSync\Filters\RejectLandlordTables;
use Marshmallow\LaravelDatabaseSync\Filters\FilterSuiteTableOption;
use Marshmallow\LaravelDatabaseSync\Filters\FilterExclusiveTableOption;

class ApplyTableFiltersAction
{
    public static function handle(
        array $tables,
        Config $config,
        DatabaseSyncCommand $command,
    ): Collection {
        return collect($tables)
            ->reject(fn($table) => RejectTables::apply($table))
            ->filter(fn($table) => FilterExclusiveTableOption::apply($table, $command->option('table')))
            ->filter(fn($table) => FilterSuiteTableOption::apply($table, $command->option('suite')))
            ->reject(fn($table) => RejectLandlordTables::apply($table, $config->multi_tenant_database_type))
            ->reject(fn($table) => RejectTenantTables::apply($table, $config->remote_database, $config->multi_tenant_database_type));
    }
}
