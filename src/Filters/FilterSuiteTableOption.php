<?php

namespace Marshmallow\LaravelDatabaseSync\Filters;

class FilterSuiteTableOption
{
    public static function apply(string $table, ?string $suite = null): bool
    {
        if ($suite) {
            $suite_tables = config("database-sync.suites.{$suite}", []);
            return in_array($table, $suite_tables);
        }

        return true;
    }
}
