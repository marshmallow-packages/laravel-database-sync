# Sync Timestamp Management

## Problem

Previously, the sync date was recorded when each table completed syncing and when the entire sync process completed. This meant that if a sync started at 13:00 and took 5 minutes, the last sync date would be recorded as 13:05. This could result in missing data that was created during the sync process (e.g., data created at 13:01-13:04).

## Solution

The sync timestamp is now captured at the beginning of the sync process and only stored in the cache when the sync completes successfully. This ensures that:

1. **No data is missed**: The sync timestamp represents when the sync started, not when it finished
2. **Atomic updates**: The cache is only updated if the entire sync process succeeds
3. **Rollback on failure**: If the sync fails, the cache remains unchanged with the previous sync date

## Implementation

### New Properties

-   `Config::$sync_start_time`: Captures the timestamp when sync begins

### New Actions

-   `LogLastSyncDateForTableWithTimestampAction`: Logs table-specific sync date with a provided timestamp
-   `LogLastSyncDateValueToStorageWithTimestampAction`: Logs global sync date with a provided timestamp

### Flow

1. **Sync Start**: Capture `sync_start_time` in the config when DatabaseSync is constructed
2. **Table Processing**: Each table is processed, but sync dates are not immediately stored
3. **Success**: If all tables sync successfully, update cache with the `sync_start_time`
4. **Failure**: If any step fails, cache remains unchanged and an error is reported

### Error Handling

The sync process now has comprehensive error handling:

-   If file copy fails, an exception is thrown
-   If database import fails, an exception is thrown
-   If the entire sync fails, the cache is not updated and the previous sync date is preserved
-   Users are informed that the sync date was not updated due to failure

## Benefits

1. **Data Integrity**: No risk of missing data created during the sync process
2. **Reliability**: Failed syncs don't corrupt the sync state
3. **Visibility**: Clear error messages when syncs fail
4. **Consistency**: All table sync dates use the same start timestamp

## Example

```
Sync starts at:     13:00:00
Table 1 synced at:  13:01:30
Table 2 synced at:  13:03:15
Table 3 synced at:  13:04:45
Sync completes at:  13:05:00

All sync dates stored: 13:00:00 (the start time)
```

This ensures that the next sync will pick up any data created from 13:00:00 onwards, including data created at 13:01, 13:02, 13:03, and 13:04 during the previous sync.
