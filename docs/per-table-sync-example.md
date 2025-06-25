# Per-Table Sync Example

This example demonstrates how the new per-table sync tracking works.

## Example Cache Structure

After syncing some tables, your cache file (`storage/app/marshmallow/database-sync/cache.json`) will look like this:

```json
{
    "my_production_db": {
        "last_sync": "2025-06-25 14:30:00",
        "tables": {
            "users": {
                "last_sync": "2025-06-25 14:30:00"
            },
            "orders": {
                "last_sync": "2025-06-25 13:45:00"
            },
            "products": {
                "last_sync": "2025-06-24 16:20:00"
            }
        }
    }
}
```

## Example Commands

```bash
# Sync all tables (each table uses its own last sync date)
php artisan db-sync

# Sync only the users table (uses users-specific sync date)
php artisan db-sync --table=users

# View sync status for all tables
php artisan db-sync --status

# Force sync from a specific date (ignores stored dates)
php artisan db-sync --date=2025-06-20

# Sync with debug output (shows which date is used for each table)
php artisan db-sync -vvv
```

## Benefits

1. **No Data Loss**: When syncing individual tables, you won't miss data because each table tracks its own sync date
2. **Efficient Syncing**: Tables that were recently synced won't re-sync unnecessary data
3. **Better Monitoring**: The `--status` option lets you see which tables need attention
4. **Backward Compatible**: Existing installations continue to work without changes

## Migration from Global Sync Dates

If you're upgrading from a version that only tracked global sync dates:

1. Your existing global sync date will be used as a fallback for tables without specific dates
2. As you sync tables individually, they'll start tracking their own dates
3. No data will be lost during the transition
4. You can continue using the same commands as before
