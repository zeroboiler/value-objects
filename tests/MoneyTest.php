<?php

/**
 * This file is part of ZeroBoiler, licensed under the proprietary license.
 */

declare(strict_types=1);

use ZeroBoiler\ValueObjects\Money;

test('money can be created with amount and currency', function (): void {
    $money = new Money(100, 'USD');

    expect($money->amount)->toBe(100)
        ->and($money->currency)->toBe('USD');
});

test('money currency is normalized to uppercase', function (): void {
    $money = new Money(100, 'usd');

    expect($money->currency)->toBe('USD');
});

test('money can be added', function (): void {
    $money1 = new Money(100, 'USD');
    $money2 = new Money(50, 'USD');

    $result = $money1->add($money2);

    expect($result->amount)->toBe(150)
        ->and($result->currency)->toBe('USD');
});

test('money addition throws on currency mismatch', function (): void {
    $money1 = new Money(100, 'USD');
    $money2 = new Money(50, 'EUR');

    expect(fn (): Money => $money1->add($money2))->toThrow(ValueError::class);
});

test('money can be subtracted', function (): void {
    $money1 = new Money(100, 'USD');
    $money2 = new Money(30, 'USD');

    $result = $money1->subtract($money2);

    expect($result->amount)->toBe(70);
});

test('money can be multiplied', function (): void {
    $money = new Money(100, 'USD');

    $result = $money->multiply(2.5);

    expect($result->amount)->toBe(250);
});

test('money can be divided', function (): void {
    $money = new Money(100, 'USD');

    $result = $money->divide(2);

    expect($result->amount)->toBe(50);
});

test('money division by zero throws', function (): void {
    $money = new Money(100, 'USD');

    expect(fn (): Money => $money->divide(0))->toThrow(ValueError::class);
});

test('money can check if zero', function (): void {
    expect(new Money(0, 'USD')->isZero())->toBeTrue()
        ->and(new Money(100, 'USD')->isZero())->toBeFalse();
});

test('money can check if positive', function (): void {
    expect(new Money(100, 'USD')->isPositive())->toBeTrue()
        ->and(new Money(-100, 'USD')->isPositive())->toBeFalse()
        ->and(new Money(0, 'USD')->isPositive())->toBeFalse();
});

test('money can check if negative', function (): void {
    expect(new Money(-100, 'USD')->isNegative())->toBeTrue()
        ->and(new Money(100, 'USD')->isNegative())->toBeFalse()
        ->and(new Money(0, 'USD')->isNegative())->toBeFalse();
});

test('money can be formatted', function (): void {
    $money = new Money(123456, 'USD');

    expect($money->format())->toBeString();
});

test('money can convert to major units', function (): void {
    $money = new Money(100, 'USD');

    expect($money->toMajor())->toBe(1.0);
});

test('money can be created from major units', function (): void {
    $money = Money::fromMajor(1.5, 'USD');

    expect($money->amount)->toBe(150);
});

test('money equals compares by value', function (): void {
    $money1 = new Money(100, 'USD');
    $money2 = new Money(100, 'USD');
    $money3 = new Money(50, 'USD');

    expect($money1->equals($money2))->toBeTrue()
        ->and($money1->equals($money3))->toBeFalse();
});

// Bug fix tests: #474, #104, #103 — zero-decimal and 3-decimal currency support

test('money decimal places for standard currencies', function (): void {
    expect(new Money(100, 'USD')->decimalPlaces())->toBe(2)
        ->and(new Money(100, 'EUR')->decimalPlaces())->toBe(2)
        ->and(new Money(100, 'GBP')->decimalPlaces())->toBe(2);
});

test('money decimal places for zero-decimal currencies (#474)', function (): void {
    expect(new Money(1000, 'JPY')->decimalPlaces())->toBe(0)
        ->and(new Money(1000, 'KRW')->decimalPlaces())->toBe(0)
        ->and(new Money(1000, 'CLP')->decimalPlaces())->toBe(0)
        ->and(new Money(1000, 'VND')->decimalPlaces())->toBe(0);
});

test('money decimal places for three-decimal currencies', function (): void {
    expect(new Money(100, 'KWD')->decimalPlaces())->toBe(3)
        ->and(new Money(100, 'BHD')->decimalPlaces())->toBe(3)
        ->and(new Money(100, 'JOD')->decimalPlaces())->toBe(3);
});

test('money subunit divisor per currency', function (): void {
    expect(new Money(100, 'USD')->subunitDivisor())->toBe(100)
        ->and(new Money(100, 'JPY')->subunitDivisor())->toBe(1)
        ->and(new Money(100, 'KWD')->subunitDivisor())->toBe(1000);
});

test('money to major units for zero-decimal currencies (#104)', function (): void {
    $jpy = new Money(1234, 'JPY');

    expect($jpy->toMajor())->toBe(1234.0);
});

test('money to major units for three-decimal currencies', function (): void {
    $kwd = new Money(1234567, 'KWD'); // 1234.567 KWD

    expect($kwd->toMajor())->toBe(1234.567);
});

test('money from major units for zero-decimal currencies', function (): void {
    $jpy = Money::fromMajor(1000, 'JPY');

    expect($jpy->amount)->toBe(1000); // No subunit, so 1:1
});

test('money from major units for three-decimal currencies', function (): void {
    $kwd = Money::fromMajor(1.234, 'KWD');

    expect($kwd->amount)->toBe(1234); // 1.234 * 1000
});

test('money format does not divide by 100 for JPY (#103)', function (): void {
    $jpy = new Money(1234, 'JPY');
    $formatted = $jpy->format('ja_JP');

    // JPY has no decimals, so ¥1234 not ¥12.34
    expect($formatted)->not->toContain('.')
        ->and($jpy->toMajor())->toBe(1234.0);
});
