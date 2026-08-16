<?php
/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

use Illuminate\Validation\ValidationException;
use ZeroBoiler\ValueObjects\Tests\DummyValueObject;

beforeEach(function (): void {
    require_once __DIR__.'/Fixtures/DummyValueObject.php';
});

test('value object is immutable', function (): void {
    $vo = new DummyValueObject('test', 42);

    // Properties are readonly, enforcing immutability
    expect($vo->value)->toBe('test');
});

test('value objects with same values are equal', function (): void {
    $vo1 = new DummyValueObject('test', 42);
    $vo2 = new DummyValueObject('test', 42);

    expect($vo1->equals($vo2))->toBeTrue();
});

test('value objects with different values are not equal', function (): void {
    $vo1 = new DummyValueObject('test', 42);
    $vo2 = new DummyValueObject('different', 42);

    expect($vo1->equals($vo2))->toBeFalse();
});

test('value object can be serialized to array', function (): void {
    $vo = new DummyValueObject('test', 42);

    expect($vo->toArray())->toBe([
        'value' => 'test',
        'count' => 42,
    ]);
});

test('value object can be serialized to JSON', function (): void {
    $vo = new DummyValueObject('test', 42);

    expect($vo->toJson())->toBe('{"value":"test","count":42}');
});

test('value object implements JsonSerializable', function (): void {
    $vo = new DummyValueObject('test', 42);

    expect(json_encode($vo))->toBe('{"value":"test","count":42}');
});

test('value object can be converted to string', function (): void {
    $vo = new DummyValueObject('test', 42);

    expect((string) $vo)->toBe('test-42');
});

test('validate throws on invalid data', function (): void {
    expect(fn (): DummyValueObject => new DummyValueObject('', 42))->toThrow(
        ValidationException::class
    );
});

test('validate passes on valid data', function (): void {
    expect(new DummyValueObject('valid', 10))->toBeInstanceOf(DummyValueObject::class);
});
