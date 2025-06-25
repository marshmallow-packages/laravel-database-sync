<?php

use Illuminate\Support\Facades\Storage;
use Marshmallow\LaravelDatabaseSync\Classes\Config;
use Marshmallow\LaravelDatabaseSync\Actions\GetLastSyncDateForTableAction;
use Marshmallow\LaravelDatabaseSync\Actions\LogLastSyncDateForTableWithTimestampAction;

beforeEach(function () {
    Storage::fake('local');
});

test('can log and retrieve last sync date for specific table', function () {
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

    // Log sync date for table
    LogLastSyncDateForTableWithTimestampAction::handle($table, $config, now());

    // Retrieve sync date for table
    $syncDate = GetLastSyncDateForTableAction::handle($table, $config);

    expect($syncDate)->not()->toBeNull()
        ->and($syncDate->format('Y-m-d'))->toBe(now()->format('Y-m-d'));
});

test('falls back to global sync date when table-specific date not found', function () {
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

    // Create a cache with only global sync date
    $cache = [
        'test-remote-db' => [
            'last_sync' => '2025-06-24 10:00:00'
        ]
    ];

    Storage::disk('local')->put('marshmallow/database-sync/cache.json', json_encode($cache));

    // Try to get sync date for table that doesn't have specific sync date
    $syncDate = GetLastSyncDateForTableAction::handle('posts', $config);

    expect($syncDate)->not()->toBeNull()
        ->and($syncDate->format('Y-m-d H:i:s'))->toBe('2025-06-24 10:00:00');
});

test('different tables can have different sync dates', function () {
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

    // Log different sync dates for different tables
    LogLastSyncDateForTableWithTimestampAction::handle('users', $config, now());
    sleep(1); // Ensure different timestamps
    LogLastSyncDateForTableWithTimestampAction::handle('orders', $config, now());

    $usersSyncDate = GetLastSyncDateForTableAction::handle('users', $config);
    $ordersSyncDate = GetLastSyncDateForTableAction::handle('orders', $config);

    expect($usersSyncDate)->not()->toBeNull()
        ->and($ordersSyncDate)->not()->toBeNull()
        ->and($ordersSyncDate->isAfter($usersSyncDate))->toBeTrue();
});

test('can get all table sync dates', function () {
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

    // Log sync dates for multiple tables
    LogLastSyncDateForTableWithTimestampAction::handle('users', $config, now());
    LogLastSyncDateForTableWithTimestampAction::handle('orders', $config, now());
    LogLastSyncDateForTableWithTimestampAction::handle('products', $config, now());

    $allSyncDates = \Marshmallow\LaravelDatabaseSync\Actions\GetAllTableSyncDatesAction::handle($config);

    expect($allSyncDates)->toHaveCount(3)
        ->and($allSyncDates->pluck('table')->toArray())->toContain('users', 'orders', 'products');
});
