<?php

namespace Marshmallow\LaravelDatabaseSync\Actions;

use Illuminate\Support\Facades\Process;
use Marshmallow\LaravelDatabaseSync\Classes\Config;
use Marshmallow\LaravelDatabaseSync\Console\DatabaseSyncCommand;

class CopyRemoteFileToLocalAction
{
    public static function handle(
        Config $config,
        DatabaseSyncCommand $command,
    ): void {
        /**
         * Copy the dump file to local
         */
        if ($command->isDebug()) {
            $command->info(__('Copying file to local machine...'));
        }
        $copyCommand = "scp {$config->remote_user_and_host}:{$config->remote_temporary_file} {$config->local_temporary_file}";
        $result = Process::run($copyCommand);

        if ($result->failed()) {
            throw new \Exception(__('Failed to copy remote file to local: :error', ['error' => $result->errorOutput()]));
        }
    }
}
