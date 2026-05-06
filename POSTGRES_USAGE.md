# PostgreSQL Support

This package now supports PostgreSQL databases in addition to MySQL. MySQL remains the default driver for backward compatibility.

## Configuration

To use PostgreSQL, set the database driver in your configuration:

### Option 1: Environment Variable

Add to your `.env` file:

```bash
DATABASE_SYNC_DATABASE_DRIVER=postgres
```

### Option 2: Config File

In your `config/database-sync.php`:

```php
'database_driver' => 'postgres',
```

## PostgreSQL-Specific Settings

The package includes PostgreSQL-optimized dump flags:

```php
'postgres' => [
    'dump_action_flags' => '--no-owner --no-privileges --data-only --column-inserts --on-conflict-do-nothing',
],
```

## Database Connection Requirements

Ensure your PostgreSQL connections are properly configured in your environment:

```bash
# Remote PostgreSQL database
DATABASE_SYNC_REMOTE_DATABASE_HOST=localhost
DATABASE_SYNC_REMOTE_DATABASE_USERNAME=postgres
DATABASE_SYNC_REMOTE_DATABASE_PASSWORD=your_password

# Local PostgreSQL database  
DATABASE_SYNC_LOCAL_DATABASE_USERNAME=postgres
DATABASE_SYNC_LOCAL_DATABASE_PASSWORD=your_local_password
```

## Command Usage

Usage remains exactly the same:

```bash
# Full sync
php artisan db:sync

# Sync specific table
php artisan db:sync --table=users

# Debug mode
php artisan db:sync --debug
```

## Supported Drivers

- `mysql` (default)
- `postgres`

## Requirements

- PostgreSQL client tools (`psql`, `pg_dump`) must be installed on both local and remote systems
- SSH access to remote server (same as MySQL requirement)
- Proper PostgreSQL user permissions for dump/import operations

## Migration from MySQL

To migrate an existing MySQL setup to PostgreSQL:

1. Update your database connections to PostgreSQL
2. Set `DATABASE_SYNC_DATABASE_DRIVER=postgres` in your environment
3. Ensure PostgreSQL client tools are installed
4. Run the sync command as usual

No code changes are required - the package automatically uses the appropriate driver based on configuration.
