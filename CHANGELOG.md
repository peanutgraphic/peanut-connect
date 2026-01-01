# Changelog

All notable changes to Peanut Connect will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Rate limiting for API endpoints to prevent abuse
- Protected error log directory with .htaccess
- PHPUnit test infrastructure with Auth and Rate Limiter tests
- Vitest frontend testing setup
- OpenAPI 3.0 documentation
- React error boundary for graceful error handling
- Server-side activity logging
- Comprehensive inline documentation

### Changed
- Improved SQL query security with parameterized LIKE patterns
- Enhanced error log storage location (now in protected directory)

### Security
- Added rate limiting to bearer token authentication
- Parameterized SQL queries in self-updater cache clearing
- Protected error log files from direct web access

## [2.1.3] - 2024-12-31

### Added
- Release automation scripts (bump, package, release)
- npm scripts for version management
- GitHub release automation

### Fixed
- Version sync across all plugin files
- Duplicate file cleanup from iCloud sync

## [2.1.2] - 2024-12-30

### Changed
- Self-updater uses path parameters instead of query strings
- Improved update check caching

### Fixed
- Self-updater initialization timing for reliable update detection

## [2.1.1] - 2024-12-30

### Added
- React SPA admin interface
- Dashboard with connection status and health summary
- Health monitoring page with detailed system information
- Updates page for managing plugin/theme/core updates
- Error log viewer with filtering and export
- Settings page with permission controls
- Activity log (client-side)

### Changed
- Moved from legacy settings page to modern React interface
- Improved health check data collection

## [2.0.0] - 2024-12-29

### Added
- Error logging system for PHP errors and fatals
- Self-hosted auto-update system via peanutgraphic.com
- Peanut Suite integration for analytics sync

### Changed
- Major refactoring of API structure
- Improved authentication flow

## [1.0.0] - 2024-12-28

### Added
- Initial release
- Site health monitoring
- Plugin/theme update detection
- Remote update capability
- Secure token-based authentication
- Permission controls for manager actions
- Peanut Suite analytics integration

[Unreleased]: https://github.com/peanutgraphic/peanut-connect/compare/v2.1.3...HEAD
[2.1.3]: https://github.com/peanutgraphic/peanut-connect/compare/v2.1.2...v2.1.3
[2.1.2]: https://github.com/peanutgraphic/peanut-connect/compare/v2.1.1...v2.1.2
[2.1.1]: https://github.com/peanutgraphic/peanut-connect/compare/v2.0.0...v2.1.1
[2.0.0]: https://github.com/peanutgraphic/peanut-connect/compare/v1.0.0...v2.0.0
[1.0.0]: https://github.com/peanutgraphic/peanut-connect/releases/tag/v1.0.0
