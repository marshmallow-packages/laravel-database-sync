# Batch File Transfer Optimization

## Overview

By default, the database sync package now uses batch file transfers to minimize the number of files that need to be transferred between remote and local environments. This significantly improves sync performance, especially when syncing multiple tables.

## How It Works

### Batch Transfer Mode (Default)

In batch mode, the sync process:

1. **Dumps all tables to a single file**: Each table's data is appended to the same temporary file on the remote server
2. **Transfers once**: The single file containing all table data is transferred to the local machine
3. **Imports all data**: All table data is imported in a single operation
4. **Cleans up**: Both remote and local temporary files are removed once

This reduces the number of file transfer operations from N (number of tables) to 1.

### Individual Transfer Mode (Legacy)

In individual mode, the sync process:

1. **Dumps each table separately**: Each table is dumped to a temporary file
2. **Transfers after each table**: The file is transferred immediately after each table dump
3. **Imports each table**: Each table's data is imported separately
4. **Cleans up after each table**: Files are removed after each table sync

## Configuration

### Config File Setting

You can set the default behavior in your `config/database-sync.php` file:

```php
'file_transfer_mode' => 'batch', // or 'individual'
```

### Environment Variable

You can also control this via environment variable:

```env
DATABASE_SYNC_FILE_TRANSFER_MODE=batch
```

### Command Line Option

You can override the config setting using the command line option:

```bash
# Force individual transfers (legacy behavior)
php artisan db-sync --individual-transfers

# Use default config setting (batch mode by default)
php artisan db-sync
```

## Automatic Behavior

The package automatically uses individual transfers in these scenarios:

1. **Single table sync**: When using `--table=tablename`, individual transfers are used for backward compatibility
2. **Legacy override**: When using `--individual-transfers` flag
3. **Config setting**: When `file_transfer_mode` is set to `individual`

## Benefits of Batch Transfer

### Performance Improvements

- **Reduced network overhead**: Fewer file transfer operations
- **Lower latency impact**: Single connection setup instead of multiple
- **Faster overall sync**: Especially noticeable with many tables or slow network connections

### Bandwidth Efficiency

- **Single file compression**: SSH compression works better on larger files
- **Reduced protocol overhead**: Less SCP/SSH handshake overhead

### Resource Usage

- **Less disk I/O**: Fewer file operations on both remote and local systems
- **Reduced process spawning**: Fewer command executions

## Example Usage

```bash
# Default: Use batch transfers for all tables
php artisan db-sync

# Force individual transfers (useful for debugging or legacy compatibility)
php artisan db-sync --individual-transfers

# Single table sync (automatically uses individual transfer)
php artisan db-sync --table=users

# Sync specific suite with batch transfers
php artisan db-sync --suite=orders

# View sync status (unchanged)
php artisan db-sync --status
```

## Migration from Previous Versions

If you're upgrading from a previous version:

1. **No action required**: The new batch mode is enabled by default
2. **Backward compatibility**: All existing commands work the same way
3. **Performance improvement**: You should notice faster sync times automatically
4. **Rollback option**: Use `--individual-transfers` if you encounter any issues

## Error Handling

The batch transfer mode maintains the same error handling as individual transfers:

- If any table fails to dump, the entire sync fails
- Sync dates are only updated on successful completion
- Failed syncs don't corrupt the sync state
- Clear error messages are provided for troubleshooting

## Debugging

For debugging purposes, you can:

1. **Use individual transfers**: `--individual-transfers` to isolate table-specific issues
2. **Enable debug output**: `-vvv` to see detailed information about the sync process
3. **Check file contents**: The temporary file contains all table dumps when using batch mode

## Technical Details

### File Structure

In batch mode, the temporary SQL file contains:

```sql
-- Table 1 data
INSERT INTO table1 VALUES (...);
INSERT INTO table1 VALUES (...);

-- Table 2 data  
INSERT INTO table2 VALUES (...);
INSERT INTO table2 VALUES (...);

-- And so on...
```

### Compatibility

- **MySQL versions**: Compatible with all supported MySQL versions
- **Table types**: Works with both timestamped and stampless tables
- **Multi-tenant**: Full support for multi-tenant configurations
- **Filters**: All table filtering options work with batch transfers
