<?php

namespace Marshmallow\LaravelDatabaseSync\Actions;

use Carbon\Carbon;
use Marshmallow\LaravelDatabaseSync\Classes\Config;
use Marshmallow\LaravelDatabaseSync\Enums\SyncDateStartOption;
use Marshmallow\LaravelDatabaseSync\Console\DatabaseSyncCommand;

class GetLastSyncDateAction
{
    public static function handle(
        Config $config,
        DatabaseSyncCommand $command,
    ): Carbon {
        if ($command->option('date')) {
            return Carbon::parse($command->option('date'));
        }

        if ($last_sync_date = GetLastSyncDateValueFromStorageAction::handle($config)) {
            return $last_sync_date;
        }

        $command->alert(
            __('No last sync date found in storage for :remote_database. Please provide a date to sync from.', [
                'remote_database' => $config->remote_database,
            ])
        );
        $command->info(__('🚀 Please note that you can also provide a date using the --date option.'));

        $options = collect(SyncDateStartOption::cases())->mapWithKeys(function ($option) {
            return ["$option->value" => $option->title()];
        });

        $sync_date_option = $command->choice(
            __('From where do you want to sync :remote_database?', [
                'remote_database' => $config->remote_database,
            ]),
            $options->toArray(),
            $options->flip()->first(),
        );

        return SyncDateStartOption::from($sync_date_option)->getDate();
    }
}
