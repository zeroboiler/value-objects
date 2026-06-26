<?php

use ZeroBoiler\ValueObjects\ValueObjects\Money;
use ValueError;

test('money can be created with amount and currency', function () {
    $money = new Money(100, 'USD');

    expect($money->amount)->toBe(100)
        ->and($money->currency)->toBe('USD');
});

test('money currency is normalized to uppercase', function () {
    $money = new Money(100, 'usd');

    expect($money->currency)->toBe('USD');
});

test('money can be added', function () {
    $money1 = new Money(100, 'USD');
    $money2 = new Money(50, 'USD');

    $result = $money1->add($money2);

    expect($result->amount)->toBe(150)
        ->and($result->currency)->toBe('USD');
});

test('money addition throws on currency mismatch', function () {
    $money1 = new Money(100, 'USD');
    $money2 = new Money(50, 'EUR');

    expect(fn () => $money1->add($money2))->toThrow(ValueError::class);
});

test('money can be subtracted', function () {
    $money1 = new Money(100, 'USD');
    $money2 = new Money(30, 'USD');

    $result = $money1->subtract($money2);

    expect($result->amount)->toBe(70);
});

test('money can be multiplied', function () {
    $money = new Money(100, 'USD');

    $result = $money->multiply(2.5);

    expect($result->amount)->toBe(250);
});

test('money can be divided', function () {
    $money = new Money(100, 'USD');

    $result = $money->divide(2);

    expect($result->amount)->toBe(50);
});

test('money division by zero throws', function () {
    $money = new Money(100, 'USD');

    expect(fn () => $money->divide(0))->toThrow(ValueError::class);
});

test('money can check if zero', function () {
    expect((new Money(0, 'USD'))->isZero())->toBeTrue()
        ->and((new Money(100, 'USD'))->isZero())->toBeFalse();
});

test('money can check if positive', function () {
    expect((new Money(100, 'USD'))->isPositive())->toBeTrue()
        ->and((new Money(-100, 'USD'))->isPositive())->toBeFalse()
        ->and((new Money(0, 'USD'))->isPositive())->toBeFalse();
});

test('money can check if negative', function () {
    expect((new Money(-100, 'USD'))->isNegative())->toBeTrue()
        ->and((new Money(100, 'USD'))->isNegative())->toBeFalse()
        ->and((new Money(0, 'USD'))->isNegative())->toBeFalse();
});

test('money can be formatted', function () {
    $money = new Money(123456, 'USD');

    expect($money->format())->toBeString();
});

test('money can convert to major units', function () {
    $money = new Money(100, 'USD');

    expect($money->toMajor())->toBe(1.0);
});

test('money can be created from major units', function () {
    $money = Money::fromMajor(1.5, 'USD');

    expect($money->amount)->toBe(150);
});

test('money equals compares by value', function () {
    $money1 = new Money(100, 'USD');
    $money2 = new Money(100, 'USD');
    $money3 = new Money(50, 'USD');

    expect($money1->equals($money2))->toBeTrue()
        ->and($money1->equals($money3))->toBeFalse();
});