<?php

use Illuminate\Support\Facades\Storage;
use Marshmallow\LaravelDatabaseSync\Enums\SyncDateStartOption;

beforeEach(function () {
    Storage::fake('local');
    config(['database-sync.remote_database' => 'test-remote-db']);
});

test('database sync command can be executed', function () {
    $this->artisan('db-sync')
        ->expectsChoice(
            'From where do you want to sync test-remote-db?',
            SyncDateStartOption::START_OF_DAY->value,
            [
                SyncDateStartOption::START_OF_DAY->value => SyncDateStartOption::START_OF_DAY->title(),
                SyncDateStartOption::YESTERDAY->value => SyncDateStartOption::YESTERDAY->title(),
            ]
        )
        ->assertExitCode(0);
});

test('database sync command accepts date option', function () {
    $this->artisan('db-sync', ['--date' => '2025-03-20'])
        ->assertExitCode(0);
});

test('database sync command accepts suite option', function () {
    // Configure a test suite
    config(['database-sync.suites' => [
        'test-suite' => [
            'users',
            'profiles',
        ],
    ]]);

    $this->artisan('db-sync', ['--suite' => 'test-suite'])
        ->expectsChoice(
            'From where do you want to sync test-remote-db?',
            SyncDateStartOption::START_OF_DAY->value,
            [
                SyncDateStartOption::START_OF_DAY->value => SyncDateStartOption::START_OF_DAY->title(),
                SyncDateStartOption::YESTERDAY->value => SyncDateStartOption::YESTERDAY->title(),
            ]
        )
        ->assertExitCode(0);
});

test('database sync command handles multi-tenant setup', function () {
    // Configure multi-tenant setup
    config([
        'database-sync.multi_tenant' => true,
        'database-sync.multi_tenant.landlord.database_name' => 'test_landlord',
        'database-sync.multi_tenant.tenants.database_names' => [
            'tenant1' => [
                'database_name' => 'test_tenant1',
                'remote_database' => 'test-remote-tenant1',
            ],
        ],
        'database-sync.remote_database' => 'test-remote-tenant1', // Set the remote database for the tenant
    ]);

    $this->artisan('db-sync', ['--tenant' => 'tenant1'])
        ->expectsChoice(
            'From where do you want to sync test-remote-tenant1?',
            SyncDateStartOption::START_OF_DAY->value,
            [
                SyncDateStartOption::START_OF_DAY->value => SyncDateStartOption::START_OF_DAY->title(),
                SyncDateStartOption::YESTERDAY->value => SyncDateStartOption::YESTERDAY->title(),
            ]
        )
        ->assertExitCode(0);
});

test('database sync command respects ignored tables', function () {
    // Configure ignored tables
    config(['database-sync.tables.ignore' => [
        'migrations',
        'password_resets',
    ]]);

    $this->artisan('db-sync')
        ->expectsChoice(
            'From where do you want to sync test-remote-db?',
            SyncDateStartOption::START_OF_DAY->value,
            [
                SyncDateStartOption::START_OF_DAY->value => SyncDateStartOption::START_OF_DAY->title(),
                SyncDateStartOption::YESTERDAY->value => SyncDateStartOption::YESTERDAY->title(),
            ]
        )
        ->assertExitCode(0);
});
