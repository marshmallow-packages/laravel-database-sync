<?php

namespace Marshmallow\LaravelDatabaseSync\Actions;

use Marshmallow\LaravelDatabaseSync\Classes\Config;

class RemoveRemoteFileAction
{
    public static function handle(
        Config $config,
    ): void {
        /**
         * Delete the remote SQL dump file
         */
        $process = $config->newProcess();
        $process->run("ssh {$config->remote_user_and_host} 'rm -f {$config->remote_temporary_file}'");
    }
}
