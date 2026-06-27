<?php

/**
 * This file is part of ZeroBoiler, licensed under the proprietary license.
 */

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use ZeroBoiler\ValueObjects\Money;
use ZeroBoiler\ValueObjects\ValueObjectCast;

final class TestModelWithCast extends Model
{
    #[Override]
    protected $casts = [
        'price' => ValueObjectCast::class.':'.Money::class,
    ];

    #[Override]
    protected $table = 'test_models';
}

final class TestModelWithCastable extends Model
{
    #[Override]
    protected $casts = [
        'price' => Money::class,
    ];

    #[Override]
    protected $table = 'test_models';
}

test('value object cast converts JSON to instance on get', function (): void {
    $cast = new ValueObjectCast(Money::class);
    $model = new TestModelWithCast;

    $value = $cast->get($model, 'price', '{"amount":10000,"currency":"USD"}', []);

    expect($value)->toBeInstanceOf(Money::class)
        ->and($value->amount)->toBe(10000)
        ->and($value->currency)->toBe('USD');
});

test('value object cast returns null for null value', function (): void {
    $cast = new ValueObjectCast(Money::class);
    $model = new TestModelWithCast;

    $value = $cast->get($model, 'price', null, []);

    expect($value)->toBeNull();
});

test('value object cast converts instance to JSON on set', function (): void {
    $cast = new ValueObjectCast(Money::class);
    $model = new TestModelWithCast;
    $money = new Money(15000, 'EUR');

    $value = $cast->set($model, 'price', $money, []);

    expect($value)->toBe('{"amount":15000,"currency":"EUR"}');
});

test('value object cast returns null for null instance on set', function (): void {
    $cast = new ValueObjectCast(Money::class);
    $model = new TestModelWithCast;

    $value = $cast->set($model, 'price', null, []);

    expect($value)->toBeNull();
});

test('value object cast serializes to array for JSON resources', function (): void {
    $cast = new ValueObjectCast(Money::class);
    $model = new TestModelWithCast;
    $money = new Money(20000, 'USD');

    $value = $cast->serialize($model, 'price', $money, []);

    expect($value)->toBe([
        'amount' => 20000,
        'currency' => 'USD',
    ]);
});

test('value object cast returns null on serialize for null', function (): void {
    $cast = new ValueObjectCast(Money::class);
    $model = new TestModelWithCast;

    $value = $cast->serialize($model, 'price', null, []);

    expect($value)->toBeNull();
});

test('value object cast handles invalid JSON gracefully on get', function (): void {
    $cast = new ValueObjectCast(Money::class);
    $model = new TestModelWithCast;

    $value = $cast->get($model, 'price', 'invalid-json', []);

    expect($value)->toBeNull();
});

test('castable trait provides cast class', function (): void {
    expect(Money::castUsing())->toBe(ValueObjectCast::class);
});

test('castable trait provides cast attributes', function (): void {
    $attributes = Money::getCastAttributes();

    expect($attributes[Money::class])->toBe(ValueObjectCast::class.':'.Money::class);
});
