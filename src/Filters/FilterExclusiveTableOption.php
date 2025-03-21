<?php

namespace Marshmallow\LaravelDatabaseSync\Filters;

class FilterExclusiveTableOption
{
    public static function apply(string $table, ?string $exclusive_table = null): bool
    {
        if (!$exclusive_table) {
            return true;
        }

        return $exclusive_table === $table;
    }
}
