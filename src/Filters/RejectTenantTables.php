<?php

namespace Marshmallow\LaravelDatabaseSync\Filters;

class RejectTenantTables
{
    public static function apply(string $table, string $remote_database, ?string $multi_tenant_database_type = null): bool
    {
        if ($multi_tenant_database_type === 'tenant') {
            if (in_array($table, config('database-sync.multi_tenant.tenants.tables.ignore', []))) {
                return true;
            }

            if (in_array($table, config("database-sync.multi_tenant.tenants.database_names.{$remote_database}.tables.ignore", []))) {
                return true;
            }
        }
        return false;
    }
}
