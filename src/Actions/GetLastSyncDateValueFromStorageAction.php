<?php

namespace Marshmallow\LaravelDatabaseSync\Actions;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Marshmallow\LaravelDatabaseSync\Classes\Config;

class GetLastSyncDateValueFromStorageAction
{
    public static function handle(
        Config $config,
    ): ?Carbon {
        $cache = GetCacheFromStorageAction::handle($config);

        if (!$cache) {
            return null;
        }

        $cache = Arr::get($cache, $config->remote_database);
        if (!$cache) {
            return null;
        }

        $last_sync = Arr::get($cache, 'last_sync');
        if (!$last_sync) {
            return null;
        }

        return Carbon::parse($last_sync);
    }
}
