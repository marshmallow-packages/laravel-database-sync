<?php

namespace Marshmallow\LaravelDatabaseSync\Actions;

use Carbon\Carbon;
use Marshmallow\LaravelDatabaseSync\Classes\Config;
use Marshmallow\LaravelDatabaseSync\Enums\SyncDateStartOption;
use Marshmallow\LaravelDatabaseSync\Console\DatabaseSyncCommand;

class GetLastSyncDateForTableWithFallbackAction
{
    public static function handle(
        string $table,
        Config $config,
        DatabaseSyncCommand $command,
    ): Carbon {
        // If date option is provided via command line, use it
        if ($command->option('date')) {
            return Carbon::parse($command->option('date'));
        }

        // First try to get table-specific sync date
        if ($last_sync_date = GetLastSyncDateForTableAction::handle($table, $config)) {
            if ($command->isDebug()) {
                $command->line(__("Using table-specific sync date for :table: :date", [
                    'table' => $table,
                    'date' => $last_sync_date->format('Y-m-d H:i:s'),
                ]));
            }
            return $last_sync_date;
        }

        // If no table-specific date, try global sync date
        if ($last_sync_date = GetLastSyncDateValueFromStorageAction::handle($config)) {
            if ($command->isDebug()) {
                $command->line(__("Using global sync date for :table: :date", [
                    'table' => $table,
                    'date' => $last_sync_date->format('Y-m-d H:i:s'),
                ]));
            }
            return $last_sync_date;
        }

        // If no sync date at all, prompt user for start date
        $command->alert(
            __('No last sync date found for table :table in database :remote_database. Please provide a date to sync from.', [
                'table' => $table,
                'remote_database' => $config->remote_database,
            ])
        );
        $command->info(__('🚀 Please note that you can also provide a date using the --date option.'));

        $options = collect(SyncDateStartOption::cases())->mapWithKeys(function ($option) {
            return ["$option->value" => $option->title()];
        });

        $sync_date_option = $command->choice(
            __('From where do you want to sync :table from :remote_database?', [
                'table' => $table,
                'remote_database' => $config->remote_database,
            ]),
            $options->toArray(),
            $options->flip()->first(),
        );

        return SyncDateStartOption::from($sync_date_option)->getDate();
    }
}
