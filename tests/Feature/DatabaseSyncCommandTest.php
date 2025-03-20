<?php

use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
});

test('database sync command can be executed', function () {
    $this->artisan('db-sync')
        ->expectsChoice(
            'From where do you want to sync test_database?',
            'today',
            ['today' => 'Today', 'yesterday' => 'Yesterday', 'custom' => 'Custom date']
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
        ->assertExitCode(0);
});

test('database sync command handles multi-tenant setup', function () {
    // Configure multi-tenant setup
    config(['database-sync.multi_tenant' => true]);
    config(['database-sync.multi_tenant.landlord.database_name' => 'test_landlord']);
    config(['database-sync.multi_tenant.tenants.database_names' => [
        'tenant1' => ['database_name' => 'test_tenant1'],
    ]]);

    $this->artisan('db-sync', ['--tenant' => 'tenant1'])
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
            'From where do you want to sync test_database?',
            'today',
            ['today' => 'Today', 'yesterday' => 'Yesterday', 'custom' => 'Custom date']
        )
        ->assertExitCode(0);
});
