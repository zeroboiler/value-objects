# Contributing to ZeroBoiler Value Objects

## Code Standards

- **PHP 8.5+** — `declare(strict_types=1)` on every file
- **Final classes** on all concrete value objects (Money, Email, Url, Currency, PhoneNumber, Address, Percentage, Duration, Coordinates), CastableAs, ValueObjectCast, console commands, service provider
- **`:void` return types** on all constructors (PHP 8.5)
- **Return type declarations** on all public methods
- **Base `ValueObject` is abstract** — designed for extension
- **`Castable` is a trait** — designed for composition
- **Zero TODO/FIXME markers** in production source

## Quality Checks

```bash
composer test              # Run Pest test suite
composer cs-check           # Pint style check
composer cs-fix             # Pint style fix
composer analyse            # PHPStan static analysis
composer rector             # Rector automated refactoring
composer rector-dry         # Rector dry-run
```

## Architecture

```
Contracts:
  └── ValueObject (interface) ← Canonical cross-package VO interface
  └── ValueObjectInterface (deprecated) ← Backward-compatible alias

Base:
  └── ValueObject (abstract) ← validate(), toPrimitive(), fromPrimitive(), columnType(), equals()

Infrastructure:
  ├── Castable (trait)       ← Auto-register VOs as Eloquent casts
  ├── CastableAs (attribute) ← Declare explicit reconstruction methods
  └── ValueObjectCast        ← Universal Eloquent cast for any VO

Concrete Value Objects:
  ├── Money                  ← Integer minor units, multi-currency, BCMath arithmetic
  ├── Currency               ← ISO 4217, decimal places, subunit divisor, symbol
  ├── Email                  ← Normalized + validated email (RFC 5322)
  ├── Url                    ← Parsed URL with scheme/host/path/query/fragment
  ├── PhoneNumber            ← E.164 format with country code detection
  ├── Address                ← Composite: street, city, state, postal, country
  ├── Percentage             ← 0-100% with arithmetic
  ├── Duration               ← Millisecond precision, human parsing ("2h 30m")
  └── Coordinates            ← Lat/lng with Haversine distance

Console Commands:
  ├── MakeValueObjectCommand    ← Generate new VO scaffolding
  └── ListValueObjectsCommand   ← List all VOs in the project
```

## Pull Requests

1. Fork the repository
2. Create a feature branch (`git checkout -b feat/my-feature`)
3. Ensure all quality checks pass
4. Commit with conventional prefix (`feat:`, `fix:`, `refactor:`, `docs:`, `test:`)
5. Push and open a pull request
