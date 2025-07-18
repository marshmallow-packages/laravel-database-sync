<?php

use Marshmallow\LaravelDatabaseSync\Classes\Config;

test('config can be created with valid parameters', function () {
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

    expect($config)->toBeInstanceOf(Config::class)
        ->and($config->remote_user_and_host)->toBe('test-remote-host@1.1.1.1')
        ->and($config->remote_database)->toBe('test-remote-db')
        ->and($config->local_host)->toBe('127.0.0.1')
        ->and($config->local_database)->toBe('test-local-db')
        ->and($config->process_timeout)->toBe(300); // Default timeout should be 300 seconds
});

test('config validates required parameters', function () {
    expect(fn() => Config::make(
        remote_user_and_host: '',  // Empty host should throw exception
        remote_database: 'test-remote-db',
        remote_database_username: 'test-user',
        remote_database_password: 'test-password',
        local_host: '127.0.0.1',
        local_database: 'test-local-db',
        local_database_username: 'test-user',
        local_database_password: 'test-password'
    ))->toThrow(\InvalidArgumentException::class, 'Remote host cannot be empty');
});
