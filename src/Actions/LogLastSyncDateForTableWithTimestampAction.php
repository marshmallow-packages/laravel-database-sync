<?php

namespace Marshmallow\LaravelDatabaseSync\Actions;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Marshmallow\LaravelDatabaseSync\Classes\Config;

class LogLastSyncDateForTableWithTimestampAction
{
    public static function handle(
        string $table,
        Config $config,
        Carbon $timestamp,
    ): void {
        $cache = GetCacheFromStorageAction::handle($config, default: [
            $config->remote_database => [],
        ]);

        // Log the sync date for this specific table with the provided timestamp
        Arr::set($cache, "{$config->remote_database}.tables.{$table}.last_sync", $timestamp->format('Y-m-d H:i:s'));

        // Also update the global last_sync for backward compatibility
        Arr::set($cache, "{$config->remote_database}.last_sync", $timestamp->format('Y-m-d H:i:s'));

        Storage::disk($config->cache_file_disk)->put($config->cache_file_path, json_encode($cache));
    }
}
