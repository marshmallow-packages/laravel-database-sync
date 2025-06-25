<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Marshmallow\LaravelDatabaseSync\Classes\Config;
use Marshmallow\LaravelDatabaseSync\Actions\LogLastSyncDateForTableWithTimestampAction;
use Marshmallow\LaravelDatabaseSync\Actions\LogLastSyncDateValueToStorageWithTimestampAction;
use Marshmallow\LaravelDatabaseSync\Actions\GetLastSyncDateForTableAction;

beforeEach(function () {
    Storage::fake('local');
});

test('can log table sync date with specific timestamp', function () {
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

    $table = 'users';
    $timestamp = Carbon::parse('2025-06-25 13:00:00');

    // Log sync date with specific timestamp
    LogLastSyncDateForTableWithTimestampAction::handle($table, $config, $timestamp);

    // Retrieve sync date for table
    $syncDate = GetLastSyncDateForTableAction::handle($table, $config);

    expect($syncDate)->not()->toBeNull()
        ->and($syncDate->format('Y-m-d H:i:s'))->toBe('2025-06-25 13:00:00');
});

test('can log global sync date with specific timestamp', function () {
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

    $timestamp = Carbon::parse('2025-06-25 13:00:00');

    // Log global sync date with specific timestamp
    LogLastSyncDateValueToStorageWithTimestampAction::handle($config, $timestamp);

    // Retrieve sync date from storage
    $syncDate = \Marshmallow\LaravelDatabaseSync\Actions\GetLastSyncDateValueFromStorageAction::handle($config);

    expect($syncDate)->not()->toBeNull()
        ->and($syncDate->format('Y-m-d H:i:s'))->toBe('2025-06-25 13:00:00');
});

test('config has sync start time property', function () {
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

    expect($config->sync_start_time)->toBeNull();

    $timestamp = Carbon::parse('2025-06-25 13:00:00');
    $config->sync_start_time = $timestamp;

    expect($config->sync_start_time)->not()->toBeNull()
        ->and($config->sync_start_time->format('Y-m-d H:i:s'))->toBe('2025-06-25 13:00:00');
});
