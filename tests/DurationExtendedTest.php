<?php

/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

use ZeroBoiler\ValueObjects\Duration;

// --- Sub-second precision (bug fix: fmod instead of % for float seconds) ---

test('humanReadable handles sub-second duration', function (): void {
    $duration = new Duration(500); // 0.5 seconds

    expect($duration->humanReadable())->toBe('500ms');
});

test('humanReadable shows seconds and ms together', function (): void {
    // 1500ms = 1 second 500ms
    $duration = new Duration(1500);

    expect($duration->humanReadable())->toBe('1 second 500ms');
});

test('humanReadable shows multiple seconds with ms', function (): void {
    // 2500ms = 2 seconds 500ms
    $duration = new Duration(2500);

    expect($duration->humanReadable())->toBe('2 seconds 500ms');
});

// --- Edge cases for humanReadable ---

test('humanReadable for exactly one hour', function (): void {
    $duration = Duration::fromHours(1);

    expect($duration->humanReadable())->toBe('1 hour');
});

// This test verifies the bug fix for sub-second precision
// Previously: fmod() instead of % for float seconds computation
// Previously: "0 seconds 500ms" -> now: "500ms"

test('humanReadable for complex duration', function (): void {
    // 1 hour 1 minute 1 second = 3600000 + 60000 + 1000 = 3661000
    $duration = new Duration(3661000);

    expect($duration->humanReadable())->toBe('1 hour 1 minute 1 second');
});

test('humanReadable for negative sub-second duration', function (): void {
    $duration = new Duration(-500);

    expect($duration->humanReadable())->toBe('-500ms');
});

test('humanReadable for negative complex duration', function (): void {
    // -(1 hour 30 minutes)
    $duration = new Duration(-(3600 + 1800) * 1000);

    expect($duration->humanReadable())->toBe('-1 hour 30 minutes');
});

// --- clampToZero ---

test('clampToZero returns same for positive duration', function (): void {
    $duration = new Duration(5000);

    expect($duration->clampToZero()->milliseconds)->toBe(5000);
});

test('clampToZero returns zero for negative duration', function (): void {
    $duration = new Duration(-5000);

    expect($duration->clampToZero()->milliseconds)->toBe(0);
});

test('clampToZero returns zero for zero duration', function (): void {
    $duration = new Duration(0);

    expect($duration->clampToZero()->milliseconds)->toBe(0);
});

// --- subtract with allowNegative=false ---

test('subtract throws ValueError when result negative and allowNegative is false', function (): void {
    $a = new Duration(1000);
    $b = new Duration(2000);

    expect(fn (): Duration => $a->subtract($b, allowNegative: false))
        ->toThrow(ValueError::class);
});

test('subtract allows negative when allowNegative is true', function (): void {
    $a = new Duration(1000);
    $b = new Duration(2000);

    $result = $a->subtract($b, allowNegative: true);

    expect($result->milliseconds)->toBe(-1000);
});

test('subtract default allows negative', function (): void {
    $a = new Duration(1000);
    $b = new Duration(3000);

    $result = $a->subtract($b);

    expect($result->milliseconds)->toBe(-2000);
});

// --- Chaining ---

test('add and subtract can be chained', function (): void {
    $d = new Duration(10000);
    $result = $d->add(new Duration(5000))->subtract(new Duration(3000));

    expect($result->milliseconds)->toBe(12000);
});

test('clampToZero can be chained after subtract', function (): void {
    $d = new Duration(1000);
    $result = $d->subtract(new Duration(5000))->clampToZero();

    expect($result->milliseconds)->toBe(0);
});

// --- toMinutes / toHours precision ---

test('toMinutes returns fractional minutes', function (): void {
    $duration = new Duration(90000); // 1.5 minutes

    expect($duration->toMinutes())->toBe(1.5);
});

test('toHours returns fractional hours', function (): void {
    $duration = new Duration(5400000); // 1.5 hours

    expect($duration->toHours())->toBe(1.5);
});

// --- fromSeconds / fromMinutes / fromHours with floats ---

test('fromSeconds handles float seconds', function (): void {
    $duration = Duration::fromSeconds(2.5);

    expect($duration->milliseconds)->toBe(2500);
});

test('fromMinutes handles float minutes', function (): void {
    $duration = Duration::fromMinutes(1.5);

    expect($duration->milliseconds)->toBe(90000);
});

test('fromHours handles float hours', function (): void {
    $duration = Duration::fromHours(1.5);

    expect($duration->milliseconds)->toBe(5400000);
});

test('fromSeconds handles zero', function (): void {
    $duration = Duration::fromSeconds(0);

    expect($duration->milliseconds)->toBe(0);
});

// --- equals ---

test('duration equals with same milliseconds', function (): void {
    $a = new Duration(5000);
    $b = new Duration(5000);

    expect($a->equals($b))->toBeTrue();
});

test('duration not equals with different milliseconds', function (): void {
    $a = new Duration(5000);
    $b = new Duration(6000);

    expect($a->equals($b))->toBeFalse();
});
