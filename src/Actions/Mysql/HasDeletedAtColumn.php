<?php

namespace Marshmallow\LaravelDatabaseSync\Actions\Mysql;

use Illuminate\Support\Facades\Process;
use Marshmallow\LaravelDatabaseSync\Classes\Config;

class HasDeletedAtColumn
{
    public static function handle(
        string $table,
        Config $config,
    ): bool {
        /**
         * Check if the table has a deleted_at column
         */
        $hasDeletedAtCommand = "ssh {$config->remote_user_and_host} \"mysql -u {$config->remote_database_username} -p{$config->remote_database_password} -D {$config->remote_database} -N -B -e 'SELECT COUNT(*) FROM information_schema.columns WHERE table_name = \\\"{$table}\\\" AND column_name = \\\"deleted_at\\\";'\"";

        return trim(Process::run($hasDeletedAtCommand)->output()) > 0;
    }
}
