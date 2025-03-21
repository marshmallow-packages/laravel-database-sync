<?php

namespace Marshmallow\LaravelDatabaseSync\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Marshmallow\LaravelDatabaseSync\LaravelDatabaseSyncServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            LaravelDatabaseSyncServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        // Set up test environment variables
        $app['config']->set('database-sync.remote_user_and_host', 'test-remote-host@1.1.1.1');
        $app['config']->set('database-sync.remote_database', 'test-remote-db');
        $app['config']->set('database-sync.remote_database_username', 'test-user');
        $app['config']->set('database-sync.remote_database_password', 'test-password');

        $app['config']->set('database-sync.local_host', '127.0.0.1');
        $app['config']->set('database-sync.local_database', 'test-local-db');
        $app['config']->set('database-sync.local_database_username', 'test-user');
        $app['config']->set('database-sync.local_database_password', 'test-password');
    }
}
