<?php

use Illuminate\Support\Facades\Storage;
use Marshmallow\LaravelDatabaseSync\Classes\Config;
use Marshmallow\LaravelDatabaseSync\Classes\DatabaseSync;
use Marshmallow\LaravelDatabaseSync\Console\DatabaseSyncCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Output\BufferedOutput;

beforeEach(function () {
    Storage::fake('local');
});

test('database sync uses batch transfer mode by default', function () {
    config(['database-sync.file_transfer_mode' => 'batch']);
    
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

    $command = new DatabaseSyncCommand();
    $input = new ArrayInput(['--date' => now()->format('Y-m-d')], $command->getDefinition());
    $output = new OutputStyle($input, new BufferedOutput());

    $command->setInput($input);
    $command->setOutput($output);

    $sync = new DatabaseSync($config, $command);
    
    // Use reflection to test the protected method
    $reflection = new ReflectionClass($sync);
    $method = $reflection->getMethod('shouldUseBatchTransfer');
    $method->setAccessible(true);
    
    expect($method->invoke($sync))->toBeTrue();
});

test('database sync uses individual transfer mode when explicitly requested', function () {
    config(['database-sync.file_transfer_mode' => 'batch']);
    
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

    $command = new DatabaseSyncCommand();
    $input = new ArrayInput([
        '--date' => now()->format('Y-m-d'),
        '--individual-transfers' => true
    ], $command->getDefinition());
    $output = new OutputStyle($input, new BufferedOutput());

    $command->setInput($input);
    $command->setOutput($output);

    $sync = new DatabaseSync($config, $command);
    
    // Use reflection to test the protected method
    $reflection = new ReflectionClass($sync);
    $method = $reflection->getMethod('shouldUseBatchTransfer');
    $method->setAccessible(true);
    
    expect($method->invoke($sync))->toBeFalse();
});

test('database sync uses individual transfer mode for single table sync', function () {
    config(['database-sync.file_transfer_mode' => 'batch']);
    
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

    $command = new DatabaseSyncCommand();
    $input = new ArrayInput([
        '--date' => now()->format('Y-m-d'),
        '--table' => 'users'
    ], $command->getDefinition());
    $output = new OutputStyle($input, new BufferedOutput());

    $command->setInput($input);
    $command->setOutput($output);

    $sync = new DatabaseSync($config, $command);
    
    // Use reflection to test the protected method
    $reflection = new ReflectionClass($sync);
    $method = $reflection->getMethod('shouldUseBatchTransfer');
    $method->setAccessible(true);
    
    expect($method->invoke($sync))->toBeFalse();
});

test('database sync respects config file setting for individual transfers', function () {
    config(['database-sync.file_transfer_mode' => 'individual']);
    
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

    $command = new DatabaseSyncCommand();
    $input = new ArrayInput(['--date' => now()->format('Y-m-d')], $command->getDefinition());
    $output = new OutputStyle($input, new BufferedOutput());

    $command->setInput($input);
    $command->setOutput($output);

    $sync = new DatabaseSync($config, $command);
    
    // Use reflection to test the protected method
    $reflection = new ReflectionClass($sync);
    $method = $reflection->getMethod('shouldUseBatchTransfer');
    $method->setAccessible(true);
    
    expect($method->invoke($sync))->toBeFalse();
});
