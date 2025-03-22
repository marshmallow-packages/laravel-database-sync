<?php

namespace Marshmallow\LaravelDatabaseSync\Actions\Mysql;

use Illuminate\Support\Facades\Process;
use Marshmallow\LaravelDatabaseSync\Classes\Config;
use Marshmallow\LaravelDatabaseSync\Console\DatabaseSyncCommand;
use Marshmallow\LaravelDatabaseSync\Exceptions\OutputWarningException;

class CountRecordsAction
{
    public static function handle(
        string $table,
        bool $deleted_at_available,
        Config $config,
        DatabaseSyncCommand $command,
    ): void {
        $whereClause = "(created_at >= \\\"{$config->date}\\\" OR updated_at >= \\\"{$config->date}\\\"";
        if ($deleted_at_available) {
            $whereClause .= " OR deleted_at >= \\\"{$config->date}\\\"";
        }
        $whereClause .= ")";

        $countCommand = "ssh {$config->remote_user_and_host} \"mysql -u {$config->remote_database_username} -p{$config->remote_database_password} -D {$config->remote_database} -N -B -e 'SELECT COUNT(*) FROM {$table} WHERE {$whereClause};'\"";

        $count = Process::run($countCommand)->output();
        $count = trim($count);
        $count = intval($count);

        if ($count === 0) {
            throw new OutputWarningException(__(":table: no new, updated or deleted records found", [
                'table' => $table,
            ]));
        }

        $command->info(__(":table: syncing :count records", [
            'table' => $table,
            'count' => $count,
        ]));
    }
}
