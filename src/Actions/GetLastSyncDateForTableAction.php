<?php

namespace Marshmallow\LaravelDatabaseSync\Actions;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Marshmallow\LaravelDatabaseSync\Classes\Config;

class GetLastSyncDateForTableAction
{
    public static function handle(
        string $table,
        Config $config,
    ): ?Carbon {
        $cache = GetCacheFromStorageAction::handle($config);

        if (!$cache) {
            return null;
        }

        $database_cache = Arr::get($cache, $config->remote_database);
        if (!$database_cache) {
            return null;
        }

        $table_last_sync = Arr::get($database_cache, "tables.{$table}.last_sync");
        if (!$table_last_sync) {
            // Fall back to global last_sync for backward compatibility
            $global_last_sync = Arr::get($database_cache, 'last_sync');
            if (!$global_last_sync) {
                return null;
            }
            return Carbon::parse($global_last_sync);
        }

        return Carbon::parse($table_last_sync);
    }
}
