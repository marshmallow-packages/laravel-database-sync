<?php

namespace Marshmallow\LaravelDatabaseSync\Actions\Postgres;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Process;
use Marshmallow\LaravelDatabaseSync\Classes\Config;
use Marshmallow\LaravelDatabaseSync\Console\DatabaseSyncCommand;

class CollectTableAction
{
    public static function handle(Config $config, DatabaseSyncCommand $command): Collection
    {
        $ignore_table_query = collect(config('database-sync.tables.ignore'))
            ->map(fn($table) => "AND table_name != '{$table}'")
            ->implode(' ');

        $query = "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_type = 'BASE TABLE' {$ignore_table_query} AND (table_name IN (SELECT table_name FROM information_schema.columns WHERE column_name IN ('created_at', 'updated_at') AND table_schema = 'public'))";

        $psqlCommand = "ssh {$config->remote_user_and_host} \"PGPASSWORD='{$config->remote_database_password}' psql -h {$config->remote_database_host} -U {$config->remote_database_username} -d {$config->remote_database} -t -c \\\"{$query}\\\"\"";

        $process = Process::timeout($config->process_timeout);
        $result = $process->run($psqlCommand);

        if ($result->failed()) {
            throw new \Exception(__('Failed to collect tables: :error', ['error' => $result->errorOutput()]));
        }

        return collect(explode("\n", trim($result->output())))
            ->map(fn($table) => trim($table))
            ->filter()
            ->values();
    }
}
