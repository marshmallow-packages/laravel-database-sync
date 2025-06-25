<?php

namespace Marshmallow\LaravelDatabaseSync\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Marshmallow\LaravelDatabaseSync\Classes\Config;

class LogLastSyncDateForTableAction
{
    public static function handle(
        string $table,
        Config $config,
    ): void {
        $cache = GetCacheFromStorageAction::handle($config, default: [
            $config->remote_database => [],
        ]);

        // Log the sync date for this specific table
        Arr::set($cache, "{$config->remote_database}.tables.{$table}.last_sync", now()->format('Y-m-d H:i:s'));

        // Also update the global last_sync for backward compatibility
        Arr::set($cache, "{$config->remote_database}.last_sync", now()->format('Y-m-d H:i:s'));

        Storage::disk($config->cache_file_disk)->put($config->cache_file_path, json_encode($cache));
    }
}
