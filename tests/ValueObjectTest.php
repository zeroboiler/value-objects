<?php

use Illuminate\Support\Facades\Validator;
use ZeroBoiler\ValueObjects\Tests\DummyValueObject;

beforeEach(function () {
    require_once __DIR__.'/Fixtures/DummyValueObject.php';
});

test('value object is immutable', function () {
    $vo = new DummyValueObject('test', 42);

    expect(fn () => $vo->value = 'modified')->toThrow(Error::class);
});

test('value objects with same values are equal', function () {
    $vo1 = new DummyValueObject('test', 42);
    $vo2 = new DummyValueObject('test', 42);

    expect($vo1->equals($vo2))->toBeTrue();
});

test('value objects with different values are not equal', function () {
    $vo1 = new DummyValueObject('test', 42);
    $vo2 = new DummyValueObject('different', 42);

    expect($vo1->equals($vo2))->toBeFalse();
});

test('value object can be serialized to array', function () {
    $vo = new DummyValueObject('test', 42);

    expect($vo->toArray())->toBe([
        'value' => 'test',
        'count' => 42,
    ]);
});

test('value object can be serialized to JSON', function () {
    $vo = new DummyValueObject('test', 42);

    expect($vo->toJson())->toBe('{"value":"test","count":42}');
});

test('value object implements JsonSerializable', function () {
    $vo = new DummyValueObject('test', 42);

    expect(json_encode($vo))->toBe('{"value":"test","count":42}');
});

test('value object can be converted to string', function () {
    $vo = new DummyValueObject('test', 42);

    expect((string) $vo)->toBe('test-42');
});

test('validate throws on invalid data', function () {
    expect(fn () => new DummyValueObject('', 42))->toThrow(
        \Illuminate\Validation\ValidationException::class
    );
});

test('validate passes on valid data', function () {
    expect(new DummyValueObject('valid', 10))->toBeInstanceOf(DummyValueObject::class);
});