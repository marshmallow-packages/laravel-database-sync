<?php

namespace Marshmallow\LaravelDatabaseSync\Enums;

use Carbon\Carbon;

enum SyncDateStartOption: string
{
    case START_OF_DAY = 'start_of_day';
    case YESTERDAY = 'yesterday';

    public function title(): string
    {
        return match ($this) {
            self::START_OF_DAY => __('Start of day'),
            self::YESTERDAY => __('Yesterday'),
        };
    }

    public function getDate(): Carbon
    {
        return match ($this) {
            self::START_OF_DAY => now()->startOfDay(),
            self::YESTERDAY => now()->subDays(1)->startOfDay(),
        };
    }
}
