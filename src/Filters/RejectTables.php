<?php

namespace Marshmallow\LaravelDatabaseSync\Filters;

class RejectTables
{
    public static function apply(string $table): bool
    {
        return in_array($table, config('database-sync.tables.ignore', []));
    }
}
