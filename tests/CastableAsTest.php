<?php

/**
 * This file is part of ZeroBoiler, licensed under the proprietary license.
 */

declare(strict_types=1);

use ReflectionClass;
use ZeroBoiler\ValueObjects\CastableAs;

// --- Construction & properties ---

test('castable as can be constructed with default null values', function (): void {
    $attr = new CastableAs;

    expect($attr->fromArray)->toBeNull()
        ->and($attr->toArray)->toBeNull();
});

test('castable as can be constructed with fromArray only', function (): void {
    $attr = new CastableAs(fromArray: 'fromDatabase');

    expect($attr->fromArray)->toBe('fromDatabase')
        ->and($attr->toArray)->toBeNull();
});

test('castable as can be constructed with toArray only', function (): void {
    $attr = new CastableAs(toArray: 'toDatabase');

    expect($attr->toArray)->toBe('toDatabase')
        ->and($attr->fromArray)->toBeNull();
});

test('castable as can be constructed with both parameters', function (): void {
    $attr = new CastableAs(fromArray: 'fromDatabase', toArray: 'toDatabase');

    expect($attr->fromArray)->toBe('fromDatabase')
        ->and($attr->toArray)->toBe('toDatabase');
});

// --- Immutability (readonly) ---

test('castable as properties are readonly', function (): void {
    $attr = new CastableAs(fromArray: 'fromDb');

    expect(fn (): mixed => $attr->fromArray = 'modified')
        ->toThrow(Error::class);
});

// --- Attribute target ---

test('castable as is an attribute targeting classes', function (): void {
    $reflection = new ReflectionClass(CastableAs::class);
    $attributes = $reflection->getAttributes(Attribute::class);

    expect($attributes)->toHaveCount(1);

    $instance = $attributes[0]->newInstance();

    expect($instance->flags)->toBe(Attribute::TARGET_CLASS);
});

// --- Final class ---

test('castable as is final', function (): void {
    $reflection = new ReflectionClass(CastableAs::class);

    expect($reflection->isFinal())->toBeTrue();
});

// --- Real usage on a class ---

test('castable as can be applied to a class and retrieved via reflection', function (): void {
    $testClass = new #[CastableAs(fromArray: 'fromArray', toArray: 'toArray')] class {};

    $reflection = new ReflectionClass($testClass);
    $attributes = $reflection->getAttributes(CastableAs::class);

    expect($attributes)->toHaveCount(1);

    $instance = $attributes[0]->newInstance();

    expect($instance->fromArray)->toBe('fromArray')
        ->and($instance->toArray)->toBe('toArray');
});
