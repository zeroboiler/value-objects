# Changelog

## [1.49.0] - 2026-08-17

### Fixed
- README version badge sync (1.46.0 → 1.49.0)
- README test suite sync (1047+ assertions across 38 test files)

## [1.48.0] - 2026-08-17

### Added
- `forMessage()` factory method + constructor with default message to both leaf exceptions (InvalidValueObjectsArgumentException, ValueObjectsRuntimeException)
- Phase 48 production readiness test (50+ assertions): exception hierarchy cross-references, factory method audit, PSR-12 compliance, phpstan parity, ServiceProvider #[Override], composer integrity, file counts

### Changed
- Version bump to 1.48.0

## [1.47.0] - 2026-08-16

### Fixed
- **PSR-12 compliance** — Removed blank line after `<?php` opening tag in 56 source and test files.
- **phpstan.neon parity** — Synced `phpstan.neon` with `phpstan.neon.dist`: added `treatPhpDocTypesAsCertain(false)`, `reportUnmatchedIgnoredErrors(false)`.

### Changed
- **Exception hierarchy @see audit** — All exceptions now use FQCN `@see` references. Bidirectional @see between base and all leaf exceptions.

### Added
- **Phase46ProductionReadinessTest** — 30+ assertions: PSR-12 audit, phpstan parity, exception hierarchy bidirectional FQCN @see, ServiceProvider/Facade finality, composer metadata, project structure, strict_types/@since/TODO audit, file counts.
- **Project structure files** — Added `.editorconfig` and `.gitattributes`.
- **Version sweep** — composer.json 1.46.0 → 1.47.0.


## [1.46.0] - 2026-08-16

### Added
- Phase 45 production readiness test (30+ assertions): comprehensive audit of 22 source files (strict_types, license headers, zero TODO/FIXME), final class enforcement on 14 classes, constructor :void return types on 14 classes, exception hierarchy (abstract ValueObjectsException with :void → 2 final leaves), ValueObject base implements ValueObjectContract, ServiceProvider finality + empty provides, 2 console commands finality, composer metadata integrity (PHP 8.5, namespace, provider, scripts, license), project structure files

### Changed
- Version bump to 1.46.0
- Updated README assertion metrics: 998+ assertions across 37 test files

## [1.37.0] - 2026-08-14

### Fixed
- ValueObjectsException base constructor `$previous` parameter type changed from `?Exception` to `?\Throwable`

### Added
- Phase 31 production audit test: strict_types (22 files), license headers, zero TODO/FIXME, exception hierarchy, composer metadata

## [1.35.0] - 2026-08-14

### Added
- Phase 28 production audit — 26 new assertions (544+ → 570+)
- Value object interface compliance, exception hierarchy, CastableAs readonly
- Service class finality, source/test file count verification

### Changed
- Version bump to 1.35.0
- Updated README assertion metrics (544+ → 570+)

## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME


## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

All notable changes to the package will be documented in this file.
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME


## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

## [1.22.0] - 2026-08-12
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME


## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

### Changed
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

- Updated README assertion metrics: 683+ assertions across 21 test files (verified via grep)
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME


## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

## [1.21.0] - 2026-08-12
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME


## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

### Changed
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

- Corrected README assertion metrics: 603+ assertions across 22 test files (verified)
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME


## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

### Verified
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

- Manual code review: all 19 source files verified for PHP 8.5 strict types, return types, final classes, #[Override], constructor :void, docblocks
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME


## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

## [1.20.0] - 2026-08-12
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME


## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

### Changed
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

- Version bump to 1.20.0
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

- Deep manual code review: all source files verified — strict types, final classes, #[Override], docblocks, return types
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

- Fixed CHANGELOG version mismatch (was 1.18.0, composer was 1.19.0)
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME


## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

## [1.15.0] - 2026-08-10
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME


## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

### Added
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

- `pestphp/pest-plugin-type-coverage` to require-dev
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME


## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

### Changed
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

- PHPStan level upgraded from 6 to 8 with `checkUnusedParameters` and `checkUninitializedProperties`
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

- Version bump to 1.15.0
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME


## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

## [1.14.0] - 2026-08-09
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME


## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

### Added
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

- `@since 1.0.0` annotations on console commands (`ListValueObjectsCommand`, `MakeValueObjectCommand`)
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

- Console commands added to Phase234 `@since` verification test
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME


## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

### Changed
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

- README version badge updated to 1.14.0
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME


## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

## [1.13.0] - 2026-08-09
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME


## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

### Added
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

- `@since 1.0.0` annotations on all 17 public classes, traits, and interfaces
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

- Phase234 test: `@since` annotation verification for all public classes
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

- Phase234 test: `provides()` method `#[Override]` verification
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME


## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

## [1.10.1] - 2026-08-08
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME


## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

### Changed
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

- Phase 2-3-4 production readiness audit confirmed — all source files verified
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME


## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME


## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME


## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

## [1.10.0] - 2026-08-08
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME


## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

### Fixed
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

- Fixed `Address` constructor formatting: separated `): void` and `{` to distinct lines for consistent style
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

- Fixed README phone number example: replaced asterisk-masked number with valid E.164 number
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME


## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

### Removed
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

- Removed legacy `CHANGES.md` (duplicate of `CHANGELOG.md`)
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

- Removed redundant `phpunit.xml` (superseded by `pest.xml`)
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME


## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

## [1.9.0] - 2026-08-08
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME


## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

### Added
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

- Phase 2-3-4 production test suite (`Phase234ProductionTest.php`)
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME


## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

## [1.8.2] - 2026-08-07
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME


## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

### Fixed
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

- Added missing `:void` return type to constructors in Coordinates, Currency, Duration, Email, Money, Percentage, PhoneNumber, Url for PHP 8.5 strict compliance.
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

- Fixed duplicate `:void` declarations from initial automated fix.
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME


## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

## [1.7.0] - 2026-08-07
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME


## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

### Added
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

- Added `CONTRIBUTING.md` with code standards, quality commands, and architecture overview
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

- Phase 2-3-4 production readiness audit — all 19 source files pass quality checks
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME


## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

## [1.6.0] - 2026-08-07
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME


## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

### Fixed
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

- Removed duplicate `#[Override]` attributes from all value object classes (Email, Url, PhoneNumber, Money, Currency, Percentage, Address, Coordinates, Duration)
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

- Duplicate attributes affected `toPrimitive()` in all VOs and `equals()` in Currency
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME


## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

## [1.5.0] - 2026-08-07
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME


## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

### Fixed
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

- Added :void return types to all constructors
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME


## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

## [1.4.0] - 2026-08-06
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME


## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

### Changed
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

- Remove IMP-2 R42 reference from Money docblock
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME


## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

## [1.3.0] - 2026-08-06
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME


## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

### Added
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

- ProductionReadinessTest with 25+ structural checks
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

- Mark ValueObjectCast final
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME


## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

## [1.0.0] - 2026-08-01
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME


## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

### Added
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

- Comprehensive value object library: Email, Url, PhoneNumber, Money, Percentage, Address, Coordinates, Duration, Currency
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

- All VOs extend abstract ValueObject base with equality, toArray, jsonSerialize
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

- ValueObjectCast for Eloquent attribute casting (automatic VO reconstruction)
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

- CastableAs attribute for custom serialization strategies
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

- MakeValueObject console command for generating new VOs
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

- VoColumnRegistry for TableBuilder integration
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

- Config-driven architecture with ServiceProvider
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

- Final service classes and exception hierarchy
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

- PHP 8.5 attributes, readonly properties, strict types
## [1.28.0] - 2026-08-13

### Fixed
- Added missing `:void` return type on `ValueObjectsException::__construct()`

### Verified
- Phase 2-3-4 production audit: PHP 8.5 strict_types, @since annotations, final classes, exception hierarchy, zero TODO/FIXME

