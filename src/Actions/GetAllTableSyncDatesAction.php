<?php

namespace Marshmallow\LaravelDatabaseSync\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Marshmallow\LaravelDatabaseSync\Classes\Config;

class GetAllTableSyncDatesAction
{
    public static function handle(Config $config): Collection
    {
        $cache = GetCacheFromStorageAction::handle($config);

        if (!$cache) {
            return collect();
        }

        $database_cache = Arr::get($cache, $config->remote_database);
        if (!$database_cache) {
            return collect();
        }

        $tables = Arr::get($database_cache, 'tables', []);

        return collect($tables)->map(function ($table_data, $table_name) {
            return [
                'table' => $table_name,
                'last_sync' => $table_data['last_sync'] ?? null,
            ];
        })->filter(function ($item) {
            return $item['last_sync'] !== null;
        })->sortBy('table');
    }
}
