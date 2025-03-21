<?php

namespace Marshmallow\LaravelDatabaseSync\Actions;

use Illuminate\Support\Facades\Process;
use Marshmallow\LaravelDatabaseSync\Classes\Config;

class RemoveRemoteFileAction
{
    public static function handle(
        Config $config,
    ): void {
        /**
         * Delete the remote SQL dump file
         */
        Process::run("ssh {$config->remote_user_and_host} 'rm -f {$config->remote_temporary_file}'");
    }
}
