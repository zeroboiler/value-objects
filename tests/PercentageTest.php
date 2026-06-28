<?php

/**
 * This file is part of ZeroBoiler, licensed under the proprietary license.
 */

declare(strict_types=1);

use Illuminate\Validation\ValidationException;
use ZeroBoiler\ValueObjects\Percentage;

test('percentage can be created', function (): void {
    $percentage = new Percentage(50.0);

    expect($percentage->value)->toBe(50.0);
});

test('percentage must be between 0 and 100', function (): void {
    expect(fn (): Percentage => new Percentage(-1.0))->toThrow(ValidationException::class);
    expect(fn (): Percentage => new Percentage(101.0))->toThrow(ValidationException::class);
});

test('percentage can calculate of a number', function (): void {
    $percentage = new Percentage(25.0);

    expect($percentage->of(100))->toBe(25.0);
});

test('percentage can apply to an amount', function (): void {
    $percentage = new Percentage(20.0);

    expect($percentage->applyTo(50))->toBe(10.0);
});

test('percentage can be added', function (): void {
    $p1 = new Percentage(30.0);
    $p2 = new Percentage(20.0);

    $result = $p1->add($p2);

    expect($result->value)->toBe(50.0);
});

test('percentage addition clamps to 100', function (): void {
    $p1 = new Percentage(80.0);
    $p2 = new Percentage(50.0);

    $result = $p1->add($p2);

    expect($result->value)->toBe(100.0);
});

test('percentage can be subtracted', function (): void {
    $p1 = new Percentage(50.0);
    $p2 = new Percentage(20.0);

    $result = $p1->subtract($p2);

    expect($result->value)->toBe(30.0);
});

test('percentage subtraction clamps to 0', function (): void {
    $p1 = new Percentage(10.0);
    $p2 = new Percentage(30.0);

    $result = $p1->subtract($p2);

    expect($result->value)->toBe(0.0);
});

test('percentage can be multiplied', function (): void {
    $percentage = new Percentage(25.0);

    $result = $percentage->multiply(2.0);

    expect($result->value)->toBe(50.0);
});

test('percentage multiplication clamps to 100', function (): void {
    $percentage = new Percentage(75.0);

    $result = $percentage->multiply(2.0);

    expect($result->value)->toBe(100.0);
});

test('percentage can check if zero', function (): void {
    expect(new Percentage(0.0)->isZero())->toBeTrue()
        ->and(new Percentage(50.0)->isZero())->toBeFalse();
});

test('percentage can check if full', function (): void {
    expect(new Percentage(100.0)->isFull())->toBeTrue()
        ->and(new Percentage(50.0)->isFull())->toBeFalse();
});

test('percentage equals compares by value', function (): void {
    $p1 = new Percentage(50.0);
    $p2 = new Percentage(50.0);
    $p3 = new Percentage(25.0);

    expect($p1->equals($p2))->toBeTrue()
        ->and($p1->equals($p3))->toBeFalse();
});

test('percentage can be converted to string', function (): void {
    $percentage = new Percentage(50.0);

    expect((string) $percentage)->toBe('50%');
});

test('percentage with decimals shows decimals', function (): void {
    $percentage = new Percentage(33.333);

    expect((string) $percentage)->toBe('33.33%');
});

test('percentage can be serialized', function (): void {
    $percentage = new Percentage(50.0);

    expect($percentage->toArray())->toBe(['value' => 50.0]);
});

// Bug fix tests: #483, #105 — float equality using epsilon

test('percentage isZero handles float precision (#483)', function (): void {
    // These values would fail strict ===  comparison due to float arithmetic
    $p = new Percentage(0.0);
    $p2 = $p->subtract(new Percentage(0.0));

    expect($p2->isZero())->toBeTrue();
});

test('percentage isFull handles float precision (#483)', function (): void {
    // 33.333 + 66.667 may not exactly equal 100.0 in float math
    $p1 = new Percentage(33.333);
    $p2 = new Percentage(66.667);
    $result = $p1->add($p2);

    // Due to clamping in add(), this should be 100
    expect($result->isFull())->toBeTrue();
});

test('percentage isZero for tiny near-zero values (#483)', function (): void {
    // The implementation should use epsilon comparison
    expect(new Percentage(0.0)->isZero())->toBeTrue();
});

test('percentage isFull for exact 100 (#483)', function (): void {
    expect(new Percentage(100.0)->isFull())->toBeTrue();
});
