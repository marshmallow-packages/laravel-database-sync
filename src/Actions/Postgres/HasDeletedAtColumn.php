<?php

namespace Marshmallow\LaravelDatabaseSync\Actions\Postgres;

use Illuminate\Support\Facades\Process;
use Marshmallow\LaravelDatabaseSync\Classes\Config;

class HasDeletedAtColumn
{
    public static function handle(string $table, Config $config): bool
    {
        $query = "SELECT COUNT(*) FROM information_schema.columns WHERE table_name = '{$table}' AND column_name = 'deleted_at' AND table_schema = 'public'";

        $psqlCommand = "ssh {$config->remote_user_and_host} \"PGPASSWORD='{$config->remote_database_password}' psql -h {$config->remote_database_host} -U {$config->remote_database_username} -d {$config->remote_database} -t -c \\\"{$query}\\\"\"";

        $process = Process::timeout($config->process_timeout);
        $result = $process->run($psqlCommand);

        if ($result->failed()) {
            throw new \Exception(__('Failed to check deleted_at column: :error', ['error' => $result->errorOutput()]));
        }

        return (int) trim($result->output()) > 0;
    }
}
