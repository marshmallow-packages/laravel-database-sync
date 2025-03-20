<?php

namespace Marshmallow\LaravelDatabaseSync\Filters;

class RejectLandlordTables
{
    public static function apply(string $table, ?string $multi_tenant_database_type = null): bool
    {
        if ($multi_tenant_database_type === 'landlord') {
            return in_array($table, config('database-sync.multi_tenant.landlord.tables.ignore', []));
        }
        return false;
    }
}
