# Changelog

All notable changes to the `zeroboiler/value-objects` package will be documented in this file.

## [1.8.1] - 2026-08-07

### Fixed
- Added missing `:void` return type to constructors in Coordinates, Currency, Duration, Email, Money, Percentage, PhoneNumber, Url for PHP 8.5 strict compliance.
- Fixed duplicate `:void` declarations from initial automated fix.

## [1.7.0] - 2026-08-07

### Added
- Added `CONTRIBUTING.md` with code standards, quality commands, and architecture overview
- Phase 2-3-4 production readiness audit — all 19 source files pass quality checks

## [1.6.0] - 2026-08-07

### Fixed
- Removed duplicate `#[Override]` attributes from all value object classes (Email, Url, PhoneNumber, Money, Currency, Percentage, Address, Coordinates, Duration)
- Duplicate attributes affected `toPrimitive()` in all VOs and `equals()` in Currency

## [1.5.0] - 2026-08-07

### Fixed
- Added :void return types to all constructors

## [1.4.0] - 2026-08-06

### Changed
- Remove IMP-2 R42 reference from Money docblock

## [1.3.0] - 2026-08-06

### Added
- ProductionReadinessTest with 25+ structural checks
- Mark ValueObjectCast final

## [1.0.0] - 2026-08-01

### Added
- Comprehensive value object library: Email, Url, PhoneNumber, Money, Percentage, Address, Coordinates, Duration, Currency
- All VOs extend abstract ValueObject base with equality, toArray, jsonSerialize
- ValueObjectCast for Eloquent attribute casting (automatic VO reconstruction)
- CastableAs attribute for custom serialization strategies
- MakeValueObject console command for generating new VOs
- VoColumnRegistry for TableBuilder integration
- Config-driven architecture with ServiceProvider
- Final service classes and exception hierarchy
- PHP 8.5 attributes, readonly properties, strict types
