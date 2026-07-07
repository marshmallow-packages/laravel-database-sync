<?php

use Marshmallow\LaravelDatabaseSync\Classes\Config;

test('config sets custom timeout from configuration', function () {
    config(['database-sync.process_timeout' => 600]);

    $config = Config::make(
        remote_user_and_host: 'test-remote-host@1.1.1.1',
        remote_database: 'test-remote-db',
        remote_database_username: 'test-user',
        remote_database_password: 'test-password',
        local_host: '127.0.0.1',
        local_database: 'test-local-db',
        local_database_username: 'test-user',
        local_database_password: 'test-password'
    );

    expect($config->process_timeout)->toBe(600);
});

test('config handles null timeout for disabling timeout', function () {
    config(['database-sync.process_timeout' => null]);

    $config = Config::make(
        remote_user_and_host: 'test-remote-host@1.1.1.1',
        remote_database: 'test-remote-db',
        remote_database_username: 'test-user',
        remote_database_password: 'test-password',
        local_host: '127.0.0.1',
        local_database: 'test-local-db',
        local_database_username: 'test-user',
        local_database_password: 'test-password'
    );

    expect($config->process_timeout)->toBeNull();
});

test('newProcess applies the configured timeout', function () {
    config(['database-sync.process_timeout' => 600]);

    $config = Config::make(
        remote_user_and_host: 'test-remote-host@1.1.1.1',
        remote_database: 'test-remote-db',
        remote_database_username: 'test-user',
        remote_database_password: 'test-password',
        local_host: '127.0.0.1',
        local_database: 'test-local-db',
        local_database_username: 'test-user',
        local_database_password: 'test-password'
    );

    expect($config->newProcess()->timeout)->toBe(600);
});

test('newProcess disables the timeout when configured as null', function () {
    config(['database-sync.process_timeout' => null]);

    $config = Config::make(
        remote_user_and_host: 'test-remote-host@1.1.1.1',
        remote_database: 'test-remote-db',
        remote_database_username: 'test-user',
        remote_database_password: 'test-password',
        local_host: '127.0.0.1',
        local_database: 'test-local-db',
        local_database_username: 'test-user',
        local_database_password: 'test-password'
    );

    // A null timeout must NOT reach Process::timeout() (CarbonInterval|int),
    // which throws a TypeError on null. forever() sets timeout to null instead.
    expect($config->newProcess()->timeout)->toBeNull();
});
