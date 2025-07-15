# Troubleshooting Large Database Sync Issues

## Timeout Errors

If you encounter timeout errors like:

```
The process "mysql -h 127.0.0.1 -u root -p'***' example_database < ~/Downloads/new_data.sql" exceeded the timeout of 60 seconds.
```

### Solution

Configure the `process_timeout` setting in your `config/database-sync.php` file:

```php
// Set timeout in seconds (default: 300 seconds / 5 minutes)
'process_timeout' => 600, // 10 minutes

// Or set to null to disable timeout entirely for very large databases
'process_timeout' => null,
```

Or set via environment variable:

```env
DATABASE_SYNC_PROCESS_TIMEOUT=600
```

### Affected Operations

The timeout setting applies to:

-   MySQL dump operations (`mysqldump`)
-   MySQL import operations (`mysql`)
-   File transfer operations (`scp`)
-   Remote file operations (`ssh rm`)

### Recommendations by Database Size

-   **Small databases (< 100MB)**: Default timeout (300 seconds) should be sufficient
-   **Medium databases (100MB - 1GB)**: Set timeout to 600-1800 seconds (10-30 minutes)
-   **Large databases (> 1GB)**: Consider setting timeout to `null` to disable it entirely

### Alternative Solutions

1. **Use batch transfer mode**: This is enabled by default and combines all table dumps into a single file transfer, which is more efficient for large databases.

2. **Sync specific tables**: Use the `--table` option to sync only specific tables:

    ```bash
    php artisan db-sync --table=users
    ```

3. **Use suites**: Group related tables and sync them separately:

    ```bash
    php artisan db-sync --suite=orders
    ```

4. **Increase MySQL timeouts**: You may also need to adjust MySQL server timeouts:
    ```sql
    SET global net_read_timeout=600;
    SET global net_write_timeout=600;
    ```

## Memory Issues

For very large datasets, you may need to adjust PHP memory limits:

```bash
php -d memory_limit=512M artisan db-sync
```

## Network Issues

If you experience network-related timeouts during file transfers:

1. Ensure stable SSH connection to remote server
2. Consider using compression in SSH config
3. Check available disk space on both local and remote systems
4. Verify network bandwidth between servers
