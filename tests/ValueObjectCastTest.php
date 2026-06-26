<?php

use Illuminate\Database\Eloquent\Model;
use ZeroBoiler\ValueObjects\Casts\ValueObjectCast;
use ZeroBoiler\ValueObjects\ValueObjects\Money;

final class TestModelWithCast extends Model
{
    protected $casts = [
        'price' => ValueObjectCast::class.':'.Money::class,
    ];

    protected $table = 'test_models';
}

final class TestModelWithCastable extends Model
{
    protected $casts = [
        'price' => Money::class,
    ];

    protected $table = 'test_models';
}

test('value object cast converts JSON to instance on get', function () {
    $cast = new ValueObjectCast(Money::class);
    $model = new TestModelWithCast;

    $value = $cast->get($model, 'price', '{"amount":10000,"currency":"USD"}', []);

    expect($value)->toBeInstanceOf(Money::class)
        ->and($value->amount)->toBe(10000)
        ->and($value->currency)->toBe('USD');
});

test('value object cast returns null for null value', function () {
    $cast = new ValueObjectCast(Money::class);
    $model = new TestModelWithCast;

    $value = $cast->get($model, 'price', null, []);

    expect($value)->toBeNull();
});

test('value object cast converts instance to JSON on set', function () {
    $cast = new ValueObjectCast(Money::class);
    $model = new TestModelWithCast;
    $money = new Money(15000, 'EUR');

    $value = $cast->set($model, 'price', $money, []);

    expect($value)->toBe('{"amount":15000,"currency":"EUR"}');
});

test('value object cast returns null for null instance on set', function () {
    $cast = new ValueObjectCast(Money::class);
    $model = new TestModelWithCast;

    $value = $cast->set($model, 'price', null, []);

    expect($value)->toBeNull();
});

test('value object cast serializes to array for JSON resources', function () {
    $cast = new ValueObjectCast(Money::class);
    $model = new TestModelWithCast;
    $money = new Money(20000, 'USD');

    $value = $cast->serialize($model, 'price', $money, []);

    expect($value)->toBe([
        'amount' => 20000,
        'currency' => 'USD',
    ]);
});

test('value object cast returns null on serialize for null', function () {
    $cast = new ValueObjectCast(Money::class);
    $model = new TestModelWithCast;

    $value = $cast->serialize($model, 'price', null, []);

    expect($value)->toBeNull();
});

test('value object cast handles invalid JSON gracefully on get', function () {
    $cast = new ValueObjectCast(Money::class);
    $model = new TestModelWithCast;

    $value = $cast->get($model, 'price', 'invalid-json', []);

    expect($value)->toBeNull();
});

test('castable trait provides cast class', function () {
    expect(Money::castUsing())->toBe(ValueObjectCast::class);
});

test('castable trait provides cast attributes', function () {
    $attributes = Money::getCastAttributes();

    expect($attributes[Money::class])->toBe(ValueObjectCast::class.':'.Money::class);
});