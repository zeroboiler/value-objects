<?php

/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

use ZeroBoiler\ValueObjects\Currency;

test('currency can be created from a valid code', function (): void {
    $currency = new Currency('USD');

    expect($currency->code)->toBe('USD');
});

test('currency normalizes code to uppercase', function (): void {
    expect(new Currency('usd')->code)->toBe('USD')
        ->and(new Currency(' eUr ')->code)->toBe('EUR');
});

test('currency throws on invalid code', function (): void {
    expect(fn (): Currency => new Currency('XYZ'))->toThrow(ValueError::class);
});

test('currency can be created via fromCode', function (): void {
    expect(Currency::fromCode('EUR')->code)->toBe('EUR');
});

test('currency decimal places for standard currencies', function (): void {
    expect(new Currency('USD')->decimalPlaces())->toBe(2)
        ->and(new Currency('EUR')->decimalPlaces())->toBe(2)
        ->and(new Currency('GBP')->decimalPlaces())->toBe(2);
});

test('currency decimal places for zero-decimal currencies', function (): void {
    expect(new Currency('JPY')->decimalPlaces())->toBe(0)
        ->and(new Currency('KRW')->decimalPlaces())->toBe(0)
        ->and(new Currency('CLP')->decimalPlaces())->toBe(0)
        ->and(new Currency('VND')->decimalPlaces())->toBe(0)
        ->and(new Currency('ISK')->decimalPlaces())->toBe(0);
});

test('currency decimal places for three-decimal currencies', function (): void {
    expect(new Currency('BHD')->decimalPlaces())->toBe(3)
        ->and(new Currency('KWD')->decimalPlaces())->toBe(3)
        ->and(new Currency('JOD')->decimalPlaces())->toBe(3)
        ->and(new Currency('OMR')->decimalPlaces())->toBe(3)
        ->and(new Currency('TND')->decimalPlaces())->toBe(3);
});

test('currency subunit divisor', function (): void {
    expect(new Currency('USD')->subunitDivisor())->toBe(100)
        ->and(new Currency('JPY')->subunitDivisor())->toBe(1)
        ->and(new Currency('KWD')->subunitDivisor())->toBe(1000);
});

test('currency subunit name', function (): void {
    expect(new Currency('USD')->subunitName())->toBe('cent')
        ->and(new Currency('GBP')->subunitName())->toBe('pence')
        ->and(new Currency('JPY')->subunitName())->toBe('sen')
        ->and(new Currency('BHD')->subunitName())->toBe('fils')
        ->and(new Currency('TRY')->subunitName())->toBe('unit');
});

test('currency equals compares by code', function (): void {
    expect(new Currency('USD')->equals(new Currency('USD')))->toBeTrue()
        ->and(new Currency('USD')->equals(new Currency('EUR')))->toBeFalse();
});

test('currency isValid static method', function (): void {
    expect(Currency::isValid('USD'))->toBeTrue()
        ->and(Currency::isValid('usd'))->toBeTrue()
        ->and(Currency::isValid('XYZ'))->toBeFalse()
        ->and(Currency::isValid(''))->toBeFalse();
});

test('currency validCodes returns all codes', function (): void {
    $codes = Currency::validCodes();

    expect($codes)->toBeArray()
        ->and($codes)->toContain('USD')
        ->and($codes)->toContain('EUR')
        ->and($codes)->toContain('JPY')
        ->and(count($codes))->toBeGreaterThan(150);
});

test('currency symbol returns a string', function (): void {
    $currency = new Currency('USD');

    expect($currency->symbol())->toBeString();
});

test('currency to array', function (): void {
    expect(new Currency('USD')->toArray())->toBe([
        'code' => 'USD',
        'decimal_places' => 2,
    ]);
});

test('currency to string returns code', function (): void {
    expect((string) new Currency('EUR'))->toBe('EUR');
});
