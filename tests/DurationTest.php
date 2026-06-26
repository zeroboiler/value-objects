<?php

use ZeroBoiler\ValueObjects\ValueObjects\Duration;

test('duration can be created from milliseconds', function () {
    $duration = new Duration(5000);

    expect($duration->milliseconds)->toBe(5000);
});

test('duration cannot be negative', function () {
    expect(fn () => new Duration(-100))->toThrow(\Illuminate\Validation\ValidationException::class);
});

test('duration can be created from seconds', function () {
    $duration = Duration::fromSeconds(5);

    expect($duration->milliseconds)->toBe(5000);
});

test('duration can be created from fractional seconds', function () {
    $duration = Duration::fromSeconds(2.5);

    expect($duration->milliseconds)->toBe(2500);
});

test('duration can be created from minutes', function () {
    $duration = Duration::fromMinutes(2);

    expect($duration->milliseconds)->toBe(120000);
});

test('duration can be created from hours', function () {
    $duration = Duration::fromHours(1);

    expect($duration->milliseconds)->toBe(3600000);
});

test('duration can convert to seconds', function () {
    $duration = new Duration(5000);

    expect($duration->toSeconds())->toBe(5.0);
});

test('duration can convert to minutes', function () {
    $duration = new Duration(120000);

    expect($duration->toMinutes())->toBe(2.0);
});

test('duration can convert to hours', function () {
    $duration = new Duration(3600000);

    expect($duration->toHours())->toBe(1.0);
});

test('duration can be added', function () {
    $d1 = new Duration(1000);
    $d2 = new Duration(500);

    $result = $d1->add($d2);

    expect($result->milliseconds)->toBe(1500);
});

test('duration can be subtracted', function () {
    $d1 = new Duration(1000);
    $d2 = new Duration(300);

    $result = $d1->subtract($d2);

    expect($result->milliseconds)->toBe(700);
});

test('duration subtraction clamps to zero', function () {
    $d1 = new Duration(500);
    $d2 = new Duration(1000);

    $result = $d1->subtract($d2);

    expect($result->milliseconds)->toBe(0);
});

test('duration can format human readable', function () {
    $duration = new Duration(9030000); // 2 hours 30 minutes 30 seconds

    expect($duration->humanReadable())->toBeString();
});

test('duration human readable shows hours minutes seconds', function () {
    $duration = Duration::fromHours(2)->add(Duration::fromMinutes(15))->add(Duration::fromSeconds(30));

    expect($duration->humanReadable())->toContain('hours')
        ->toContain('minutes')
        ->toContain('seconds');
});

test('duration equals compares by value', function () {
    $d1 = new Duration(5000);
    $d2 = new Duration(5000);
    $d3 = new Duration(6000);

    expect($d1->equals($d2))->toBeTrue()
        ->and($d1->equals($d3))->toBeFalse();
});

test('duration can be converted to string', function () {
    $duration = new Duration(5000);

    expect((string) $duration)->toBeString();
});

test('duration can be serialized', function () {
    $duration = new Duration(5000);

    expect($duration->toArray())->toBe(['milliseconds' => 5000]);
});