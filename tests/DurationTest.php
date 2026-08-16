<?php
/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

use ZeroBoiler\ValueObjects\Duration;

test('duration can be created from milliseconds', function (): void {
    $duration = new Duration(5000);

    expect($duration->milliseconds)->toBe(5000);
});

test('duration can be negative (for representing differences)', function (): void {
    $duration = new Duration(-100);

    expect($duration->milliseconds)->toBe(-100);
});

test('duration can be created from seconds', function (): void {
    $duration = Duration::fromSeconds(5);

    expect($duration->milliseconds)->toBe(5000);
});

test('duration can be created from fractional seconds', function (): void {
    $duration = Duration::fromSeconds(2.5);

    expect($duration->milliseconds)->toBe(2500);
});

test('duration can be created from minutes', function (): void {
    $duration = Duration::fromMinutes(2);

    expect($duration->milliseconds)->toBe(120000);
});

test('duration can be created from hours', function (): void {
    $duration = Duration::fromHours(1);

    expect($duration->milliseconds)->toBe(3600000);
});

test('duration can convert to seconds', function (): void {
    $duration = new Duration(5000);

    expect($duration->toSeconds())->toBe(5.0);
});

test('duration can convert to minutes', function (): void {
    $duration = new Duration(120000);

    expect($duration->toMinutes())->toBe(2.0);
});

test('duration can convert to hours', function (): void {
    $duration = new Duration(3600000);

    expect($duration->toHours())->toBe(1.0);
});

test('duration can be added', function (): void {
    $d1 = new Duration(1000);
    $d2 = new Duration(500);

    $result = $d1->add($d2);

    expect($result->milliseconds)->toBe(1500);
});

test('duration can be subtracted', function (): void {
    $d1 = new Duration(1000);
    $d2 = new Duration(300);

    $result = $d1->subtract($d2);

    expect($result->milliseconds)->toBe(700);
});

test('duration subtraction allows negative results', function (): void {
    $d1 = new Duration(500);
    $d2 = new Duration(1000);

    $result = $d1->subtract($d2);

    expect($result->milliseconds)->toBe(-500);
});

test('duration subtraction can clamp to zero', function (): void {
    $d1 = new Duration(500);
    $d2 = new Duration(1000);

    $result = $d1->subtract($d2)->clampToZero();

    expect($result->milliseconds)->toBe(0);
});

test('duration can format human readable', function (): void {
    $duration = new Duration(9030000); // 2 hours 30 minutes 30 seconds

    expect($duration->humanReadable())->toBeString();
});

test('duration human readable shows hours minutes seconds', function (): void {
    $duration = Duration::fromHours(2)->add(Duration::fromMinutes(15))->add(Duration::fromSeconds(30));

    expect($duration->humanReadable())->toContain('hours')
        ->toContain('minutes')
        ->toContain('seconds');
});

test('duration equals compares by value', function (): void {
    $d1 = new Duration(5000);
    $d2 = new Duration(5000);
    $d3 = new Duration(6000);

    expect($d1->equals($d2))->toBeTrue()
        ->and($d1->equals($d3))->toBeFalse();
});

test('duration can be converted to string', function (): void {
    $duration = new Duration(5000);

    expect((string) $duration)->toBeString();
});

test('duration can be serialized', function (): void {
    $duration = new Duration(5000);

    expect($duration->toArray())->toBe(['milliseconds' => 5000]);
});
