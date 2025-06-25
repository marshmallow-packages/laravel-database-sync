# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

-   **Batch file transfer optimization**: All tables are now transferred in a single file by default to minimize network overhead and improve sync performance
-   New `--individual-transfers` command option to use legacy behavior (one file per table)
-   Configuration option `file_transfer_mode` to control default transfer behavior (`batch` or `individual`)
-   Environment variable `DATABASE_SYNC_FILE_TRANSFER_MODE` to configure transfer mode
-   Automatic fallback to individual transfers when syncing single tables (`--table` option)
-   Comprehensive documentation for batch transfer optimization
-   **Per-table sync date tracking**: Each table now maintains its own last sync date to prevent data loss when syncing individual tables
-   New `--status` option to view sync history for all tables
-   `GetLastSyncDateForTableAction` for retrieving table-specific sync dates
-   `LogLastSyncDateForTableAction` for storing table-specific sync dates
-   `GetLastSyncDateForTableWithFallbackAction` for intelligent sync date resolution with fallback support
-   `GetAllTableSyncDatesAction` for retrieving sync status of all tables
-   Debug output showing which sync date is being used for each table
-   **Sync timestamp management**: Sync start time is now captured at the beginning and only stored on successful completion
-   `LogLastSyncDateForTableWithTimestampAction` for logging table sync dates with specific timestamps, `LogLastSyncDateForTableAction` is deleted because we dont use it anymore.
-   `LogLastSyncDateValueToStorageWithTimestampAction` for logging global sync dates with specific timestamps, `LogLastSyncDateValueToStorageAction` is deleted because we dont use it anymore.
-   Enhanced error handling for file copy and database import operations

### Changed

-   **Breaking**: Default behavior now uses batch file transfers instead of individual transfers for improved performance
-   Command description updated to reflect new batch transfer capabilities and options
-   Sync process now uses table-specific dates when available, with automatic fallback to global dates
-   Improved sync status information showing the date being used for each table
-   Enhanced command description to mention the new `--status` option
-   **Breaking**: Sync dates are now recorded at sync start time, not completion time, preventing data loss during long-running syncs
-   Sync process now has comprehensive error handling with rollback capability

### Fixed

-   **Performance**: Significantly reduced file transfer overhead by batching all tables into a single transfer operation
-   Typo in sync message: "We will no start" → "We will now start"
-   **Critical**: Fixed potential data loss issue where data created during sync could be missed due to timestamps being recorded at completion rather than start

## [1.0.0] - 2025-03-21

### Added

-   Initial release of Laravel Database Sync package
-   Command `db-sync` to synchronize data from a remote database to local
-   Support for multi-tenant database setups
-   Suite configuration for selective table synchronization
-   Date-based synchronization with options for today and yesterday
-   Configurable ignored tables
-   Support for Laravel 11.x and 12.x
-   Support for PHP 8.2 and 8.3
-   Comprehensive test suite with GitHub Actions CI
-   Storage system for tracking last sync dates
