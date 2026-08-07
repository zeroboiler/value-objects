# Changelog

All notable changes to the `zeroboiler/value-objects` package will be documented in this file.

## [1.5.0] - 2026-08-07

### Changed
- Removed internal tracking reference (IMP-2 R42) from Money::allocate() docblock

## [1.4.0] - 2026-08-06

### Added
- `ProductionReadinessTest` with 25+ structural checks (strict types, final classes, trait usage, composer validation)

### Changed
- Version bump to 1.4.0

## [1.3.0] - 2026-08-06

### Changed
- Mark ValueObjectCast final

## [1.2.0] - 2026-08-06

### Fixed
- Fixed README namespace references: corrected `ZeroBoiler\ValueObjects\ValueObjects\*` to `ZeroBoiler\ValueObjects\*`
- Fixed README License section: corrected from "Proprietary" to "MIT"
- Version bump to 1.2.0

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
