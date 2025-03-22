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
    -   [Table Configuration](#table-configuration)
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

````env
DATABASE_SYNC_REMOTE_USER_AND_HOST="forge@1.1.1.1"
DATABASE_SYNC_REMOTE_DATABASE_USERNAME=forge
DATABASE_SYNC_REMOTE_DATABASE_PASSWORD=

DATABASE_SYNC_TEMPORARY_FILE_LOCATION_REMOTE=~/new_data.sql
DATABASE_SYNC_TEMPORARY_FILE_LOCATION_LOCAL=~/Downloads/new_data.sql

> **Important**: When connecting to a Forge-provisioned database server, you must use the main database user that was created during the initial server provisioning. Other database users created afterward may not have the necessary privileges to execute the required database commands for synchronization.

## Usage

### Basic Synchronization

To sync your remote database to local:

```bash
php artisan db-sync
````

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
