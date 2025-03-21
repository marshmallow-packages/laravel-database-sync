# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-03-21

### Added

- Initial release of Laravel Database Sync package
- Command `db-sync` to synchronize data from a remote database to local
- Support for multi-tenant database setups
- Suite configuration for selective table synchronization
- Date-based synchronization with options for today and yesterday
- Configurable ignored tables
- Support for Laravel 11.x and 12.x
- Support for PHP 8.2 and 8.3
- Comprehensive test suite with GitHub Actions CI
- Storage system for tracking last sync dates
