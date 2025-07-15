<?php

namespace Marshmallow\LaravelDatabaseSync\Actions;

use Illuminate\Support\Facades\Process;
use Marshmallow\LaravelDatabaseSync\Classes\Config;

class RemoveLocalFileAction
{
    public static function handle(
        Config $config,
    ): void {
        /**
         * Delete the local SQL dump file
         */
        $process = Process::timeout($config->process_timeout);
        $process->run("rm -f {$config->local_temporary_file}");
    }
}
