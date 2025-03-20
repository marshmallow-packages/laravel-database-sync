<?php

namespace Marshmallow\LaravelDatabaseSync\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Marshmallow\LaravelDatabaseSync\Classes\Config;

class LogLastSyncDateValueToStorageAction
{
    public static function handle(
        Config $config,
    ): void {
        $cache = GetCacheFromStorageAction::handle($config, default: [
            $config->remote_database => [],
        ]);

        Arr::set($cache, "{$config->remote_database}.last_sync", now()->format('Y-m-d H:i:s'));
        Storage::disk($config->cache_file_disk)->put($config->cache_file_path, json_encode($cache));
    }
}
