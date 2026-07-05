<?php

/**
 * This file is part of ZeroBoiler, licensed under the proprietary license.
 */

declare(strict_types=1);

use ZeroBoiler\ValueObjects\Currency;
use ZeroBoiler\ValueObjects\Money;

// --- Overflow detection tests (#4) ---

test('money add throws OverflowException on positive overflow', function (): void {
    $max = new Money(PHP_INT_MAX, 'USD');
    $one = new Money(1, 'USD');

    expect(fn (): Money => $max->add($one))->toThrow(OverflowException::class);
});

test('money multiply throws OverflowException on large factor', function (): void {
    $large = new Money(PHP_INT_MAX - 100, 'USD');

    expect(fn (): Money => $large->multiply(3.0))->toThrow(OverflowException::class);
});

test('money multiply by zero returns zero', function (): void {
    $money = new Money(1000, 'USD');
    $result = $money->multiply(0.0);
    expect($result->amount)->toBe(0);
});

test('money fromMajor throws OverflowException on extreme amounts', function (): void {
    expect(fn (): Money => Money::fromMajor(1e18, 'USD'))->toThrow(OverflowException::class);
});

test('money add does not throw on normal amounts', function (): void {
    $result = new Money(100, 'USD')->add(new Money(200, 'USD'));
    expect($result->amount)->toBe(300);
});

// --- Currency VO integration (#159) ---

test('money can be created with Currency VO', function (): void {
    $currency = new Currency('EUR');
    $money = new Money(100, $currency);

    expect($money->amount)->toBe(100)
        ->and($money->currency)->toBe('EUR');
});

test('money can be created with amount and currency', function (): void {
    $money = new Money(100, 'USD');

    expect($money->amount)->toBe(100)
        ->and($money->currency)->toBe('USD');
});

test('money currency is normalized to uppercase', function (): void {
    $money = new Money(100, 'usd');

    expect($money->currency)->toBe('USD');
});

test('money currency() returns Currency VO', function (): void {
    $money = new Money(100, 'USD');
    $currency = $money->currency();

    expect($currency)->toBeInstanceOf(Currency::class)
        ->and($currency->code)->toBe('USD')
        ->and($currency->decimalPlaces())->toBe(2);
});

test('money can be created with Currency VO instance', function (): void {
    $money = new Money(500, new Currency('GBP'));

    expect($money->currency)->toBe('GBP')
        ->and($money->currency())->toBeInstanceOf(Currency::class);
});

// --- Basic arithmetic ---

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

// --- Query methods ---

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

// --- Formatting & conversion ---

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

// --- convert() tests (#159) ---

test('money convert to different currency', function (): void {
    $usd = new Money(10000, 'USD'); // $100.00
    $eur = $usd->convert('EUR', 0.85); // €85.00

    expect($eur->currency)->toBe('EUR')
        ->and($eur->amount)->toBe(8500); // 85.00 * 100 = 8500 cents
});

test('money convert with Currency VO', function (): void {
    $usd = new Money(10000, 'USD');
    $eur = $usd->convert(new Currency('EUR'), 0.85);

    expect($eur->currency)->toBe('EUR')
        ->and($eur->amount)->toBe(8500);
});

test('money convert respects target currency decimal places', function (): void {
    // USD → JPY: 1 USD = 150 JPY
    $usd = new Money(100, 'USD'); // $1.00
    $jpy = $usd->convert('JPY', 150.0);

    // 1.00 * 150 = 150 JPY (0 decimal places, so 150 minor units)
    expect($jpy->currency)->toBe('JPY')
        ->and($jpy->amount)->toBe(150);
});

test('money convert to same currency with rate 1', function (): void {
    $usd = new Money(100, 'USD');
    $result = $usd->convert('USD', 1.0);

    expect($result->amount)->toBe(100)
        ->and($result->currency)->toBe('USD');
});

test('money convert throws on non-positive rate', function (): void {
    $money = new Money(100, 'USD');

    expect(fn (): Money => $money->convert('EUR', 0.0))->toThrow(ValueError::class)
        ->and(fn (): Money => $money->convert('EUR', -0.5))->toThrow(ValueError::class);
});

test('money convert throws on invalid currency', function (): void {
    $money = new Money(100, 'USD');

    expect(fn (): Money => $money->convert('XYZ', 1.0))->toThrow(ValueError::class);
});

test('money convert handles zero amount', function (): void {
    $usd = new Money(0, 'USD');
    $eur = $usd->convert('EUR', 0.85);

    expect($eur->amount)->toBe(0)
        ->and($eur->currency)->toBe('EUR');
});

// --- allocate() tests (#159) ---

test('money allocate into equal parts', function (): void {
    $money = new Money(100, 'USD');
    $parts = $money->allocate(3);

    expect($parts)->toHaveCount(3)
        ->and($parts[0]->amount)->toBe(34) // remainder goes to first
        ->and($parts[1]->amount)->toBe(33)
        ->and($parts[2]->amount)->toBe(33);
});

test('money allocate sum equals original', function (): void {
    $money = new Money(99, 'USD');
    $parts = $money->allocate(7);

    $sum = array_sum(array_map(fn (Money $m): int => $m->amount, $parts));

    expect($sum)->toBe(99);
});

test('money allocate into 1 part returns itself', function (): void {
    $money = new Money(100, 'USD');
    $parts = $money->allocate(1);

    expect($parts)->toHaveCount(1)
        ->and($parts[0]->amount)->toBe(100)
        ->and($parts[0]->currency)->toBe('USD');
});

test('money allocate preserves currency', function (): void {
    $money = new Money(100, 'EUR');
    $parts = $money->allocate(2);

    expect($parts[0]->currency)->toBe('EUR')
        ->and($parts[1]->currency)->toBe('EUR');
});

test('money allocate throws on zero parts', function (): void {
    expect(fn (): array => new Money(100, 'USD')->allocate(0))->toThrow(ValueError::class);
});

test('money allocate throws on negative parts', function (): void {
    expect(fn (): array => new Money(100, 'USD')->allocate(-1))->toThrow(ValueError::class);
});

test('money allocate handles negative amounts', function (): void {
    $money = new Money(-100, 'USD');
    $parts = $money->allocate(3);

    $sum = array_sum(array_map(fn (Money $m): int => $m->amount, $parts));

    expect($sum)->toBe(-100);
});

test('money allocate even split when no remainder', function (): void {
    $parts = new Money(100, 'USD')->allocate(4);

    expect($parts[0]->amount)->toBe(25)
        ->and($parts[1]->amount)->toBe(25)
        ->and($parts[2]->amount)->toBe(25)
        ->and($parts[3]->amount)->toBe(25);
});

// --- allocateRatios() tests (#159) ---

test('money allocateRatios with equal ratios', function (): void {
    $parts = new Money(100, 'USD')->allocateRatios([1, 1, 2]);

    expect($parts[0]->amount)->toBe(25)
        ->and($parts[1]->amount)->toBe(25)
        ->and($parts[2]->amount)->toBe(50);
});

test('money allocateRatios sum equals original', function (): void {
    $parts = new Money(1000, 'USD')->allocateRatios([3, 7]);

    expect($parts[0]->amount + $parts[1]->amount)->toBe(1000);
});

test('money allocateRatios single ratio', function (): void {
    $parts = new Money(100, 'USD')->allocateRatios([1]);

    expect($parts)->toHaveCount(1)
        ->and($parts[0]->amount)->toBe(100);
});

test('money allocateRatios throws on empty', function (): void {
    expect(fn (): array => new Money(100, 'USD')->allocateRatios([]))->toThrow(ValueError::class);
});

test('money allocateRatios throws on negative ratio', function (): void {
    expect(fn (): array => new Money(100, 'USD')->allocateRatios([1, -1]))->toThrow(ValueError::class);
});

test('money allocateRatios throws on all-zero ratios', function (): void {
    expect(fn (): array => new Money(100, 'USD')->allocateRatios([0, 0]))->toThrow(ValueError::class);
});

// --- convertTo() and static factory tests (#592) ---

test('money convertTo is alias for convert', function (): void {
    $usd = new Money(1000, 'USD');
    $eur = $usd->convertTo('EUR', 0.92);

    expect($eur->currency)->toBe('EUR')
        ->and($eur->amount)->toBeGreaterThan(0);
});

test('money convertTo with Currency VO', function (): void {
    $usd = new Money(1000, 'USD');
    $eur = $usd->convertTo(new Currency('EUR'), 0.92);

    expect($eur->currency)->toBe('EUR');
});

test('money usd factory creates USD money', function (): void {
    $money = Money::usd(1000);

    expect($money->amount)->toBe(1000)
        ->and($money->currency)->toBe('USD');
});

test('money eur factory creates EUR money', function (): void {
    $money = Money::eur(500);

    expect($money->amount)->toBe(500)
        ->and($money->currency)->toBe('EUR');
});

test('money gbp factory creates GBP money', function (): void {
    $money = Money::gbp(2500);

    expect($money->amount)->toBe(2500)
        ->and($money->currency)->toBe('GBP');
});

test('money jpy factory creates JPY money', function (): void {
    $money = Money::jpy(5000);

    expect($money->amount)->toBe(5000)
        ->and($money->currency)->toBe('JPY');
});

// --- ExchangeRateProvider tests (#592) ---

test('money convertVia uses exchange rate provider', function (): void {
    $provider = new class implements \ZeroBoiler\ValueObjects\ExchangeRateProvider
    {
        public function getRate(string $from, string $to): float
        {
            return match ([$from, $to]) {
                ['USD', 'EUR'] => 0.92,
                ['EUR', 'USD'] => 1.087,
                default => 1.0,
            };
        }
    };

    $usd = Money::usd(1000);
    $eur = $usd->convertVia('EUR', $provider);

    expect($eur->currency)->toBe('EUR')
        ->and($eur->amount)->toBeGreaterThan(900)
        ->and($eur->amount)->toBeLessThan(950);
});

test('exchange rate provider interface exists', function (): void {
    expect(interface_exists(\ZeroBoiler\ValueObjects\ExchangeRateProvider::class))->toBeTrue();
});
