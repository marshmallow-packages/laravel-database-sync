<?php

namespace Marshmallow\LaravelDatabaseSync\Filters;

use Marshmallow\LaravelDatabaseSync\Classes\DatabaseSync;

class FilterTenantOption
{
    public static function apply(string|array $tenant_settings, string|int $tenant_key, ?string $tenant = null): bool
    {
        if (!$tenant) {
            return true;
        }

        return $tenant === DatabaseSync::getTenantDatabaseName($tenant_settings, $tenant_key);
    }
}
