# Changelog

All notable changes to the `zeroboiler/value-objects` package will be documented in this file.

## [1.1.0] - 2026-08-06

### Fixed
- Removed duplicate `minimum-stability`/`prefer-stable` keys from composer.json
- Added `sort-packages: true` to composer config
- Version bump to 1.1.0

## [1.0.0] - 2025-08-06

### Added
- Base value object abstractions with immutability and equality
- Eloquent cast integration for value objects
- Console commands: `make:value-object`, `list:value-objects`
- `ValueObjectsServiceProvider` with command registration
- MIT license
