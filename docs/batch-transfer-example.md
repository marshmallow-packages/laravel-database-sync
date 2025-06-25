# Batch Transfer Example

This example demonstrates the difference between the old individual transfer behavior and the new batch transfer optimization.

## Before: Individual Transfers (Legacy)

When syncing 5 tables with the old behavior:

```
Table 1: users
├── Dump users data → remote_file.sql
├── Copy remote_file.sql → local_file.sql  
├── Import local_file.sql
└── Cleanup files

Table 2: orders  
├── Dump orders data → remote_file.sql
├── Copy remote_file.sql → local_file.sql
├── Import local_file.sql
└── Cleanup files

Table 3: products
├── Dump products data → remote_file.sql
├── Copy remote_file.sql → local_file.sql
├── Import local_file.sql
└── Cleanup files

Table 4: categories
├── Dump categories data → remote_file.sql
├── Copy remote_file.sql → local_file.sql
├── Import local_file.sql
└── Cleanup files

Table 5: reviews
├── Dump reviews data → remote_file.sql
├── Copy remote_file.sql → local_file.sql
├── Import local_file.sql
└── Cleanup files

Total file transfers: 5
Total import operations: 5
```

## After: Batch Transfers (Optimized)

When syncing 5 tables with the new batch behavior:

```
Batch Operation:
├── Clear remote file
├── Dump users data → remote_file.sql (append)
├── Dump orders data → remote_file.sql (append)
├── Dump products data → remote_file.sql (append)
├── Dump categories data → remote_file.sql (append)
├── Dump reviews data → remote_file.sql (append)
├── Copy remote_file.sql → local_file.sql (single transfer)
├── Import local_file.sql (all tables at once)
└── Cleanup files

Total file transfers: 1
Total import operations: 1
```

## Performance Impact

### Network Operations Reduced by 80%

- **Before**: 5 file transfers + 5 imports = 10 network operations
- **After**: 1 file transfer + 1 import = 2 network operations

### Real-World Example

For a database with 20 tables:

- **Individual mode**: 20 × (dump + transfer + import + cleanup) = 80 operations
- **Batch mode**: 1 × (transfer + import + cleanup) = 3 operations

### Time Savings

Assuming each file transfer takes 2 seconds:

- **Individual mode**: 20 tables × 2 seconds = 40 seconds in transfer time
- **Batch mode**: 1 transfer × 2 seconds = 2 seconds in transfer time
- **Time saved**: 38 seconds (95% reduction in transfer time)

## File Size Considerations

The batch mode creates larger temporary files but provides better overall efficiency:

- **Compression**: SSH compression is more effective on larger files
- **Overhead**: Single connection setup vs. multiple connections
- **Bandwidth**: Better utilization of available bandwidth

## Backward Compatibility

All existing commands work exactly the same:

```bash
# These commands automatically use batch mode
php artisan db-sync
php artisan db-sync --suite=orders
php artisan db-sync --date=2025-06-20

# These commands automatically use individual mode  
php artisan db-sync --table=users
php artisan db-sync --individual-transfers

# Status command is unchanged
php artisan db-sync --status
```

## Error Handling

Error handling remains robust in both modes:

- If any table fails, the entire sync fails
- Sync dates are only updated on success
- Clear error messages for troubleshooting
- No partial state corruption
