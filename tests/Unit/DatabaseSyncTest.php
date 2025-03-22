<?php

use Marshmallow\LaravelDatabaseSync\Classes\Config;
use Marshmallow\LaravelDatabaseSync\Classes\DatabaseSync;
use Marshmallow\LaravelDatabaseSync\Console\DatabaseSyncCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Output\BufferedOutput;

test('database sync can be initialized with config', function () {
    $config = Config::make(
        remote_host: 'test-remote-host',
        remote_database: 'test-remote-db',
        remote_database_username: 'test-user',
        remote_database_password: 'test-password',
        local_host: '127.0.0.1',
        local_database: 'test-local-db',
        local_database_username: 'test-user',
        local_database_password: 'test-password'
    );

    $command = new DatabaseSyncCommand();
    $input = new ArrayInput(['--date' => now()->format('Y-m-d')], $command->getDefinition());
    $output = new OutputStyle($input, new BufferedOutput());

    $command->setInput($input);
    $command->setOutput($output);

    $sync = new DatabaseSync($config, $command);
    expect($sync)->toBeInstanceOf(DatabaseSync::class);
});

test('database sync can set and get database', function () {
    $config = Config::make(
        remote_host: 'test-remote-host',
        remote_database: 'test-remote-db',
        remote_database_username: 'test-user',
        remote_database_password: 'test-password',
        local_host: '127.0.0.1',
        local_database: 'test-local-db',
        local_database_username: 'test-user',
        local_database_password: 'test-password'
    );

    $command = new DatabaseSyncCommand();
    $input = new ArrayInput(['--date' => now()->format('Y-m-d')], $command->getDefinition());
    $output = new OutputStyle($input, new BufferedOutput());

    $command->setInput($input);
    $command->setOutput($output);

    $sync = new DatabaseSync($config, $command);
    $sync->setDatabase('new_database');

    expect($sync->getDatabase())->toBe('new_database');
});

test('database sync can handle multi-tenant database type', function () {
    $config = Config::make(
        remote_host: 'test-remote-host',
        remote_database: 'test-remote-db',
        remote_database_username: 'test-user',
        remote_database_password: 'test-password',
        local_host: '127.0.0.1',
        local_database: 'test-local-db',
        local_database_username: 'test-user',
        local_database_password: 'test-password'
    );

    $command = new DatabaseSyncCommand();
    $input = new ArrayInput(['--date' => now()->format('Y-m-d')], $command->getDefinition());
    $output = new OutputStyle($input, new BufferedOutput());

    $command->setInput($input);
    $command->setOutput($output);

    $sync = new DatabaseSync($config, $command);
    $sync->setMultiTenantDatabaseType('tenant');

    expect($sync->isMultiTenantDatabase())->toBeTrue()
        ->and($sync->getMultiTenantDatabaseType())->toBe('tenant');
});
