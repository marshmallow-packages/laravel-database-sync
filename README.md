# Laravel Database Sync

A powerful Laravel package that enables seamless synchronization of data from a remote database to your local development environment.

## Table of Contents

-   [Requirements](#requirements)
-   [Installation](#installation)
-   [Configuration](#configuration)
    -   [Environment Variables](#environment-variables)
-   [Usage](#usage)
    -   [Basic Synchronization](#basic-synchronization)
    -   [Advanced Options](#advanced-options)
    -   [Per-Table Sync Tracking](#per-table-sync-tracking)
    -   [Table Configuration](#table-configuration)
    -   [Timeout Configuration](#timeout-configuration)
    -   [Synchronization Suites](#synchronization-suites)
    -   [Multi-Tenant Support](#multi-tenant-support)
-   [Testing](#testing)
    -   [Test Structure](#test-structure)
    -   [Writing Tests](#writing-tests)
-   [Security](#security)
-   [Support](#support)
-   [License](#license)

## Requirements

-   PHP ^8.2
-   Laravel ^10.0|^11.0|^12.0

## Installation

You can install the package via composer:

```bash
composer require marshmallow/laravel-database-sync --dev
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag="database-sync-config"
```

### Environment Variables

Add these variables to your `.env` file:

```env
DATABASE_SYNC_REMOTE_USER_AND_HOST="forge@1.1.1.1"
DATABASE_SYNC_REMOTE_DATABASE_USERNAME=forge
DATABASE_SYNC_REMOTE_DATABASE_PASSWORD=

DATABASE_SYNC_TEMPORARY_FILE_LOCATION_REMOTE=~/new_data.sql
DATABASE_SYNC_TEMPORARY_FILE_LOCATION_LOCAL=~/Downloads/new_data.sql
```

> **Important**: When connecting to a Forge-provisioned database server, you must use the main database user that was created during the initial server provisioning. Other database users created afterward may not have the necessary privileges to execute the required database commands for synchronization.

## Usage

### Basic Synchronization

To sync your remote database to local:

```bash
php artisan db-sync
```

By default, the package uses **batch file transfers** for optimal performance, transferring all table data in a single file to minimize network overhead.

### Advanced Options

The sync command supports several options:

```bash
php artisan db-sync [options]
```

Available options:

-   `--date`: Sync records from a specific date
-   `--suite`: Use a predefined suite of tables
-   `--table`: Sync a specific table
-   `--tenant`: Specify tenant for multi-tenant applications
-   `--skip-landlord`: Skip landlord database in multi-tenant setup
-   `--full-sync`: Sync the full table without a date constraint
-   `--status`: View the sync history and status for all tables
-   `--individual-transfers`: Use individual file transfers for each table (legacy behavior)

### Per-Table Sync Tracking

The package now tracks the last sync date for each individual table, preventing data loss when syncing single tables. This means:

-   Each table maintains its own sync history
-   When syncing a specific table with `--table`, only that table's sync date is considered
-   The package automatically falls back to the global sync date for backward compatibility
-   You can view the sync status of all tables using the `--status` option
-   **Sync timestamps are captured at the start of the process to prevent missing data created during sync**

#### Viewing Sync Status

To see the last sync date for all tables:

```bash
php artisan db-sync --status
```

This will display a table showing each table name and its last sync date, helping you track which tables have been synced recently and which might need attention.

#### How It Works

1. **Sync Start Timestamp**: The sync timestamp is captured when the sync process begins
2. **Individual Table Tracking**: Each table's sync date is stored separately in the cache file
3. **Atomic Updates**: Cache is only updated when the entire sync process completes successfully
4. **Automatic Fallback**: If no table-specific date exists, the global sync date is used
5. **Backward Compatibility**: Existing installations continue to work without any changes
6. **Debug Information**: When running with `-vvv` (debug mode), you'll see which sync date is being used for each table
7. **Error Recovery**: If sync fails, the cache remains unchanged with the previous sync dates

### File Transfer Optimization

The package uses **batch file transfers** by default to minimize network overhead:

-   **Batch Mode (Default)**: All tables are dumped to a single file and transferred once
-   **Individual Mode**: Each table is transferred separately (legacy behavior)

#### Configuration

Control the transfer mode in `config/database-sync.php`:

```php
'file_transfer_mode' => 'batch', // or 'individual'
```

Or via environment variable:

```env
DATABASE_SYNC_FILE_TRANSFER_MODE=batch
```

#### Benefits of Batch Transfer

-   **Reduced network overhead**: Single file transfer instead of multiple
-   **Faster sync times**: Especially noticeable with many tables
-   **Better compression**: SSH compression works more efficiently on larger files
-   **Lower resource usage**: Fewer process spawns and file operations

#### When Individual Transfers Are Used

-   Single table sync (`--table=tablename`)
-   Explicit override (`--individual-transfers`)
-   Config set to `individual` mode

### Table Configuration

You can exclude specific tables from synchronization in the `config/database-sync.php` file:

```php
'tables' => [
    'ignore' => [
        'action_events',
        'jobs',
        'telescope_entries',
        'password_resets',
    ],
],
```

### Timeout Configuration

For large databases, you may need to adjust the process timeout to prevent operations from timing out:

```php
// Set timeout in seconds (default: 300 seconds / 5 minutes)
'process_timeout' => 600, // 10 minutes

// Or set to null to disable timeout entirely for very large databases
'process_timeout' => null,
```

You can also set this via environment variable:

```env
DATABASE_SYNC_PROCESS_TIMEOUT=600
```

This timeout applies to:

-   MySQL dump operations (`mysqldump`)
-   MySQL import operations (`mysql`)
-   File transfer operations (`scp`)

### Synchronization Suites

Define custom synchronization suites in the configuration file to group tables for specific sync tasks:

```php
'suites' => [
    'orders' => [
        'orders',
        'order_items',
    ],
],
```

Then use the suite option:

```bash
php artisan db-sync --suite=orders
```

### Multi-Tenant Support

The package supports multi-tenant architectures. Enable it in the configuration:

```php
'multi_tenant' => [
    'landlord' => [
        'database_name' => 'marshmallow_landord',
        'tables' => [
            'ignore' => [
                'action_events',
            ],
        ],
    ],
    'tenants' => [
        'database_names' => [
            'marshmallow_nl' => [
                'tables' => [
                    'ignore' => [
                        'users',
                    ],
                ],
            ],
            'marshmallow_dev',
            'marshmallow_io',
        ],
        'tables' => [
            'ignore' => [
                'logs',
            ],
        ],
    ],
],
```

Configure tenant-specific settings in your configuration file and use the `--tenant` option to sync specific tenant databases:

```bash
php artisan db-sync --tenant="marshmallow_nl" --skip-landlord
php artisan db-sync --tenant="marshmallow_nl" --skip-landlord --suite=orders
```

## Testing

This package uses Pest PHP for testing. To run the tests:

```bash
composer test
```

To run tests with coverage report:

```bash
composer test-coverage
```

### Test Structure

The test suite includes:

-   **Unit Tests**: Testing individual components

    -   `Config` class tests
    -   `DatabaseSync` class tests
    -   Other utility classes

-   **Feature Tests**: Testing the package functionality
    -   Command execution tests
    -   Multi-tenant functionality
    -   Suite configurations
    -   Table filtering

### Writing Tests

To add new tests, create a new test file in either the `tests/Unit` or `tests/Feature` directory. The package uses Pest's expressive syntax:

```php
test('your test description', function () {
    // Your test code
    expect($result)->toBe($expected);
});
```

## Security

-   Never commit sensitive database credentials to version control
-   Always use environment variables for sensitive information
-   Ensure proper access controls on both remote and local databases

## Support

For support, please email stef@marshmallow.dev

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
