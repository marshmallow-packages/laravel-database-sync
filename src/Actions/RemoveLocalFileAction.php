<?php

namespace Marshmallow\LaravelDatabaseSync\Actions;

use Marshmallow\LaravelDatabaseSync\Classes\Config;

class RemoveLocalFileAction
{
    public static function handle(
        Config $config,
    ): void {
        /**
         * Delete the local SQL dump file
         */
        $process = $config->newProcess();
        $process->run("rm -f {$config->local_temporary_file}");
    }
}
