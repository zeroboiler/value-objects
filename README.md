# ZeroBoiler Value Objects

![Latest Version](https://img.shields.io/badge/version-1.18.0-blue)
Immutable value objects with validation and Eloquent auto-casting for Laravel 13 / PHP 8.5.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Usage](#usage)
- [Built-in Value Objects](#built-in-value-objects)
- [Custom Value Objects](#custom-value-objects)
- [CLI Commands](#cli-commands)
- [API Resources](#api-resources)
- [Testing](#testing)
- [Quality Assurance](#quality-assurance)
- [Requirements](#requirements)
- [License](#license)

## Features

- ✅ Immutable value objects with validation
- ✅ Built-in value objects: Money, Currency, Email, PhoneNumber, Address, Percentage, Duration, Coordinates, Url
- ✅ Eloquent auto-casting support
- ✅ Castable trait for easy integration
- ✅ JSON serialization (`toArray()`, `toJson()`, `JsonSerializable`)
- ✅ Value equality comparison (`equals()`)
- ✅ String representation (`__toString()`)
- ✅ Laravel validator integration
- ✅ CLI commands for scaffolding
- ✅ Type-safe with PHP 8.5

## Installation

```bash
composer require zeroboiler/value-objects
```

### Service Provider (Laravel 13)

The service provider is auto-registered via Laravel's package discovery.

## Usage

### Basic Value Object

```php
use ZeroBoiler\ValueObjects\Email;

$email = new Email('user@example.com');

echo $email->value;              // user@example.com
echo $email->domain();           // example.com
echo $email->localPart();        // user
echo (string) $email;            // user@example.com
echo json_encode($email);        // {"email":"user@example.com"}
```

### Value Equality

```php
$email1 = new Email('user@example.com');
$email2 = new Email('USER@example.com'); // normalized to lowercase

$email1->equals($email2); // true
```

### Eloquent Integration

#### Using Castable Trait (Recommended)

```php
use Illuminate\Database\Eloquent\Model;
use ZeroBoiler\ValueObjects\Email;

class User extends Model
{
    protected $casts = [
        'email' => Email::class, // Auto-registered!
    ];
}

$user->email = new Email('user@example.com');
$user->save();

// Retrieved automatically
$email = $user->email; // Email instance
```

#### Manual Cast Registration

```php
use Illuminate\Database\Eloquent\Model;
use ZeroBoiler\ValueObjects\ValueObjectCast;
use ZeroBoiler\ValueObjects\Money;

class Product extends Model
{
    protected $casts = [
        'price' => ValueObjectCast::class.':'.Money::class,
    ];
}
```

## Built-in Value Objects

### Money

```php
use ZeroBoiler\ValueObjects\Money;
use ZeroBoiler\ValueObjects\Currency;

$price = new Money(1999, 'USD'); // $19.99 in cents

// Accepts Currency VO too
$price = new Money(1999, new Currency('USD'));

// Get the Currency VO
$price->currency(); // Currency('USD')
$price->currency()->decimalPlaces(); // 2
$price->currency()->subunitName(); // "cent"

// Arithmetic
$total = $price->add(new Money(500, 'USD'));
$discounted = $price->subtract(new Money(200, 'USD'));
$doubled = $price->multiply(2);
$halved = $price->divide(2);

// Currency conversion
$usd = new Money(10000, 'USD'); // $100.00
$eur = $usd->convert('EUR', 0.85); // €85.00
$eur = $usd->convert(new Currency('EUR'), 0.85); // same, with VO

// Allocation (split evenly with no remainder loss)
$parts = new Money(100, 'USD')->allocate(3);
// [Money(34), Money(33), Money(33)] — sum is exactly 100

// Allocation by ratios
$parts = new Money(100, 'USD')->allocateRatios([1, 1, 2]);
// [Money(25), Money(25), Money(50)]

// Queries
$price->isZero();      // bool
$price->isPositive();  // bool
$price->isNegative();  // bool

// Formatting
echo $price->format();              // $19.99
echo $price->format('en_GB');       // £19.99 (if currency is GBP)
echo $price->toMajor();             // 19.99

// Factory methods
$money = Money::fromMajor(19.99, 'USD');
```

### Currency

```php
use ZeroBoiler\ValueObjects\Currency;

$usd = new Currency('USD');
$usd->code;              // "USD"
$usd->decimalPlaces();   // 2
$usd->subunitDivisor();  // 100
$usd->subunitName();     // "cent"
$usd->symbol();          // "$"

// Zero-decimal currencies (JPY, KRW, ISK, etc.)
$jpy = new Currency('JPY');
$jpy->decimalPlaces();   // 0
$jpy->subunitDivisor();  // 1

// Three-decimal currencies (BHD, KWD, etc.)
$kwd = new Currency('KWD');
$kwd->decimalPlaces();   // 3
$kwd->subunitDivisor();  // 1000

// Validation
Currency::isValid('USD');  // true
Currency::isValid('XYZ');  // false

// Equality
$usd->equals(new Currency('USD'));  // true
$usd->equals(new Currency('EUR'));  // false
```

### Email

```php
use ZeroBoiler\ValueObjects\Email;

$email = new Email('USER@EXAMPLE.COM'); // auto-normalized to lowercase

$email->value;      // user@example.com
$email->domain();   // example.com
$email->localPart(); // user
```

### PhoneNumber (E.164)

```php
use ZeroBoiler\ValueObjects\PhoneNumber;

$phone = new PhoneNumber('+15551234567');

$phone->value;           // +15551234567
$phone->countryCode();   // 1
$phone->format();        // +1 555 123 4567 (formatted)
```

### Address

```php
use ZeroBoiler\ValueObjects\Address;

$address = new Address(
    street: '123 Main St',
    street2: 'Apt 4B',
    city: 'Springfield',
    state: 'IL',
    postalCode: '62701',
    country: 'USA'
);

$address->full();  // "123 Main St, Apt 4B, Springfield, IL 62701, USA"
$address->lines(); // ['123 Main St', 'Apt 4B', 'Springfield, IL 62701', 'USA']
```

### Percentage

```php
use ZeroBoiler\ValueObjects\Percentage;

$tax = new Percentage(8.25);
$discount = new Percentage(15);

$tax->of(100);        // 8.25
$discount->applyTo(50); // 7.5

// Arithmetic
$combined = $tax->add($discount); // 23.25
$reduced = $discount->subtract(5); // 10

// Queries
$discount->isZero();  // false
$discount->isFull();  // false
```

### Duration

```php
use ZeroBoiler\ValueObjects\Duration;

$duration = Duration::fromMinutes(90);

// Conversions
$duration->toSeconds();  // 5400
$duration->toMinutes();  // 90
$duration->toHours();    // 1.5
$duration->toDays();     // 0.0625

// Human readable
$duration->humanReadable(); // "1 hour 30 minutes"

// Parse from human-readable string
Duration::fromHuman('1h 30m');       // 5400000ms
Duration::fromHuman('2d 4h');        // 187200000ms
Duration::fromHuman('500ms');        // 500ms
Duration::fromHuman('1d 2h 30m 45s'); // full combo

// Arithmetic
$extended = $duration->add(Duration::fromMinutes(30));
$shortened = $duration->subtract(Duration::fromMinutes(15));

// Factory methods
$duration = Duration::fromSeconds(3600);
$duration = Duration::fromHours(2);
$duration = Duration::fromDays(7);    // 1 week
```

### Coordinates

```php
use ZeroBoiler\ValueObjects\Coordinates;

$ny = new Coordinates(40.7128, -74.0060);
$la = new Coordinates(34.0522, -118.2437);

// Distance calculations (Haversine formula)
$distance = $ny->distanceTo($la);      // meters
$distanceKm = $ny->distanceToKm($la);  // kilometers
$distanceMiles = $ny->distanceToMiles($la); // miles

// Validation
$ny->isValidLat(40.7128); // true
$ny->isValidLng(-74.0060); // true
```

### Url

```php
use ZeroBoiler\ValueObjects\Url;

$url = new Url('https://example.com/path?foo=bar#section');

// Components
$url->scheme();      // https
$url->host();        // example.com
$url->path();        // /path
$url->query();       // foo=bar
$url->fragment();    // section

// Query params
$url->queryParams(); // ['foo' => 'bar']

// Queries
$url->isHttps();     // true
$url->isHttp();      // false

// Modify
$httpsUrl = $url->withScheme('https');
```

## Custom Value Objects

### Using CLI Command

```bash
php artisan zeroboiler:value-object:make ProductPrice
```

This generates a scaffold in `app/ValueObjects/ProductPrice.php`.

### Manual Creation

```php
<?php

namespace App\ValueObjects;

use ZeroBoiler\ValueObjects\ValueObject;

final readonly class ProductPrice extends ValueObject
{
    use Castable; // Optional: enables Eloquent auto-casting

    public int $cents;
    public string $currency;

    public function __construct(int $cents, string $currency = 'USD')
    {
        $this->validate(
            ['cents' => $cents, 'currency' => $currency],
            [
                'cents' => 'required|integer|min:0',
                'currency' => 'required|string|size:3',
            ]
        );

        $this->cents = $cents;
        $this->currency = strtoupper($currency);
    }

    public function toArray(): array
    {
        return [
            'cents' => $this->cents,
            'currency' => $this->currency,
        ];
    }

    public function __toString(): string
    {
        return sprintf('%s %.2f', $this->currency, $this->cents / 100);
    }
}
```

## CLI Commands

### List All Value Objects

```bash
php artisan zeroboiler:value-object:list
```

Lists all ValueObject classes in `app/ValueObjects/`.

### Create Custom Value Object

```bash
php artisan zeroboiler:value-object:make CustomVO --namespace=App\ValueObjects
```

## API Resources

Value objects serialize cleanly to JSON:

```php
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'email' => $this->email, // Automatically serialized to array
            'price' => $this->price,
        ];
    }
}
```

Returns:
```json
{
  "email": {
    "email": "user@example.com"
  },
  "price": {
    "amount": 1999,
    "currency": "USD"
  }
}
```

## Testing

```bash
composer test
```

## Quality Assurance

```bash
# Lint with Pint
composer lint

# Static analysis with PHPStan
composer analyse

# Refactoring check with Rector
composer rector

# Run all checks
composer ci
```

## Requirements

- PHP 8.5+
- Laravel 13+
- ext-intl (for Money formatting)

## License

MIT. See [LICENSE](LICENSE).

## Production Readiness

This package maintains strict production-quality standards:

- **PHP 8.5 strict types** on every source file
- **Final classes** on all service, cast, and console classes
- **Return type declarations** on all public and protected methods
- **Constructor `:void` return types** (PHP 8.5 feature) on all constructors
- **No TODO/FIXME** comments in production code
- **Comprehensive test suite** with 563+ assertions across 20 test files

## Credits

Built by the ZeroBoiler team.

---

For more information, see the [ZeroBoiler documentation](https://github.com/zeroboiler).