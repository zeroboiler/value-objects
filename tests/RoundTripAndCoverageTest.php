<?php
/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

use ZeroBoiler\ValueObjects\Address;
use ZeroBoiler\ValueObjects\Coordinates;
use ZeroBoiler\ValueObjects\Currency;
use ZeroBoiler\ValueObjects\Duration;
use ZeroBoiler\ValueObjects\Email;
use ZeroBoiler\ValueObjects\Money;
use ZeroBoiler\ValueObjects\Percentage;
use ZeroBoiler\ValueObjects\PhoneNumber;
use ZeroBoiler\ValueObjects\Url;

// =========================================================================
// Money::percentage() tests (previously untested)
// =========================================================================

test('money percentage applies percentage correctly', function (): void {
    $price = new Money(10000, 'USD'); // $100.00
    $withTax = $price->percentage(19); // 19% VAT

    expect($withTax->amount)->toBe(1900)
        ->and($withTax->currency)->toBe('USD');
});

test('money percentage with zero percent returns zero', function (): void {
    $money = new Money(5000, 'USD');
    $result = $money->percentage(0);

    expect($result->amount)->toBe(0)
        ->and($result->currency)->toBe('USD');
});

test('money percentage with 100 percent returns same amount', function (): void {
    $money = new Money(5000, 'USD');
    $result = $money->percentage(100);

    expect($result->amount)->toBe(5000)
        ->and($result->currency)->toBe('USD');
});

test('money percentage with fractional percent', function (): void {
    $money = new Money(10000, 'USD'); // $100.00
    $result = $money->percentage(0.5); // 0.5%

    expect($result->amount)->toBe(50)
        ->and($result->currency)->toBe('USD');
});

test('money percentage preserves currency', function (): void {
    $money = new Money(1000, 'EUR');
    $result = $money->percentage(20);

    expect($result->currency)->toBe('EUR');
});

// =========================================================================
// Round-trip: toPrimitive → fromPrimitive for all VOs
// =========================================================================

test('email toPrimitive and fromPrimitive round-trip', function (): void {
    $original = new Email('round-trip@example.com');
    $restored = Email::fromPrimitive($original->toPrimitive());

    expect($restored->equals($original))->toBeTrue()
        ->and($restored->value)->toBe('round-trip@example.com');
});

test('url toPrimitive and fromPrimitive round-trip', function (): void {
    $original = new Url('https://round-trip.example.com/path?q=1');
    $restored = Url::fromPrimitive($original->toPrimitive());

    expect($restored->equals($original))->toBeTrue();
});

test('phone number toPrimitive and fromPrimitive round-trip', function (): void {
    $original = new PhoneNumber('+902123456789');
    $restored = PhoneNumber::fromPrimitive($original->toPrimitive());

    expect($restored->equals($original))->toBeTrue()
        ->and($restored->value)->toBe('+902123456789');
});

test('currency toPrimitive and fromPrimitive round-trip', function (): void {
    $original = new Currency('TRY');
    $restored = Currency::fromPrimitive($original->toPrimitive());

    expect($restored->equals($original))->toBeTrue()
        ->and($restored->code)->toBe('TRY');
});

test('percentage toPrimitive and fromPrimitive round-trip', function (): void {
    $original = new Percentage(33.33);
    $restored = Percentage::fromPrimitive($original->toPrimitive());

    expect($restored->value)->toBe(33.33);
});

test('duration toPrimitive and fromPrimitive round-trip', function (): void {
    $original = Duration::fromMinutes(90);
    $restored = Duration::fromPrimitive($original->toPrimitive());

    expect($restored->equals($original))->toBeTrue()
        ->and($restored->milliseconds)->toBe(5_400_000);
});

test('address toPrimitive and fromPrimitive round-trip', function (): void {
    $original = new Address('Sokak 1', null, 'İstanbul', '34', '34000', 'TR');
    $restored = Address::fromPrimitive($original->toPrimitive());

    expect($restored->equals($original))->toBeTrue()
        ->and($restored->street)->toBe('Sokak 1')
        ->and($restored->city)->toBe('İstanbul')
        ->and($restored->country)->toBe('TR');
});

test('coordinates toPrimitive and fromPrimitive round-trip', function (): void {
    $original = new Coordinates(41.0082, 28.9784);
    $restored = Coordinates::fromPrimitive($original->toPrimitive());

    expect($restored->equals($original))->toBeTrue();
});

// =========================================================================
// columnType returns valid Laravel migration types
// =========================================================================

test('email columnType is string', function (): void {
    expect(Email::columnType())->toBe('string');
});

test('url columnType is string', function (): void {
    expect(Url::columnType())->toBe('string');
});

test('phone number columnType is string', function (): void {
    expect(PhoneNumber::columnType())->toBe('string');
});

test('currency columnType is string', function (): void {
    expect(Currency::columnType())->toBe('string');
});

test('money columnType is json', function (): void {
    expect(Money::columnType())->toBe('json');
});

test('address columnType is json', function (): void {
    expect(Address::columnType())->toBe('json');
});

test('coordinates columnType is json', function (): void {
    expect(Coordinates::columnType())->toBe('json');
});

test('duration columnType is integer', function (): void {
    expect(Duration::columnType())->toBe('integer');
});

test('percentage columnType is float', function (): void {
    expect(Percentage::columnType())->toBe('float');
});

// =========================================================================
// JsonSerializable consistency
// =========================================================================

test('email jsonSerialize returns toArray output', function (): void {
    $email = new Email('json@example.com');

    expect($email->jsonSerialize())->toBe($email->toArray());
});

test('money jsonSerialize returns toArray output', function (): void {
    $money = new Money(999, 'USD');

    expect($money->jsonSerialize())->toBe($money->toArray());
});

test('address jsonSerialize returns toArray output', function (): void {
    $address = new Address('1 St', null, 'City', 'ST', '00000', 'US');

    expect($address->jsonSerialize())->toBe($address->toArray());
});

// =========================================================================
// Duration edge cases: fromDays edge cases
// =========================================================================

test('duration fromDays with negative days', function (): void {
    $duration = Duration::fromDays(-1);

    expect($duration->milliseconds)->toBe(-86_400_000)
        ->and($duration->toDays())->toBe(-1.0);
});

test('duration toDays for mixed units', function (): void {
    $duration = Duration::fromHours(36); // 1.5 days

    expect($duration->toDays())->toBe(1.5);
});

// =========================================================================
// Money: comparison operators edge cases
// =========================================================================

test('money greaterThan equal amounts returns false', function (): void {
    $a = new Money(100, 'USD');
    $b = new Money(100, 'USD');

    expect($a->greaterThan($b))->toBeFalse();
});

test('money lessThan equal amounts returns false', function (): void {
    $a = new Money(100, 'USD');
    $b = new Money(100, 'USD');

    expect($a->lessThan($b))->toBeFalse();
});

// =========================================================================
// Currency: fromCode is identical to constructor
// =========================================================================

test('currency fromCode and constructor produce equal instances', function (): void {
    $a = new Currency('USD');
    $b = Currency::fromCode('USD');

    expect($a->equals($b))->toBeTrue();
});

// =========================================================================
// ExchangeRateProvider: all public VOs listed in contract test
// =========================================================================

test('exchange rate provider getRate has correct parameter types', function (): void {
    $reflection = new ReflectionClass(\ZeroBoiler\ValueObjects\ExchangeRateProvider::class);
    $method = $reflection->getMethod('getRate');
    $params = $method->getParameters();

    expect($params)->toHaveCount(2)
        ->and($params[0]->getName())->toBe('from')
        ->and($params[1]->getName())->toBe('to');

    foreach ($params as $param) {
        $type = $param->getType();
        expect($type)->not->toBeNull();
        expect($type->getName())->toBe('string');
    }
});
