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

    // Per #659: invalid JSON throws RuntimeException instead of silently returning null
    expect(fn () => $cast->get($model, 'price', 'invalid-json', []))
        ->toThrow(RuntimeException::class);
});

test('castable trait provides cast class', function (): void {
    expect(Money::castUsing())->toBeInstanceOf(ValueObjectCast::class);
});

test('castable trait provides cast attributes', function (): void {
    $attributes = Money::getCastAttributes();

    expect($attributes[Money::class])->toBe(ValueObjectCast::class.':'.Money::class);
});

// Bug fix tests: #467 — spread operator constructor mismatch

use ZeroBoiler\ValueObjects\Email;
use ZeroBoiler\ValueObjects\PhoneNumber;
use ZeroBoiler\ValueObjects\Url;

test('value object cast reconstructs Email from toArray keys (#467)', function (): void {
    $cast = new ValueObjectCast(Email::class);
    $model = new TestModelWithCast;

    // Email::toArray() returns ['email' => '...'] but constructor takes $email param
    $value = $cast->get($model, 'email', '{"email":"test@example.com"}', []);

    expect($value)->toBeInstanceOf(Email::class)
        ->and((string) $value)->toBe('test@example.com');
});

test('value object cast reconstructs PhoneNumber from toArray keys (#467)', function (): void {
    $cast = new ValueObjectCast(PhoneNumber::class);
    $model = new TestModelWithCast;

    // PhoneNumber::toArray() returns ['phone' => '...'] but constructor takes $phoneNumber
    $value = $cast->get($model, 'phone', '{"phone":"+1234567890"}', []);

    expect($value)->toBeInstanceOf(PhoneNumber::class)
        ->and((string) $value)->toBe('+1234567890');
});

test('value object cast reconstructs Url from toArray keys (#467)', function (): void {
    $cast = new ValueObjectCast(Url::class);
    $model = new TestModelWithCast;

    // Url::toArray() returns multiple keys including 'url', 'scheme', etc.
    // Constructor only takes $url
    $json = json_encode(['url' => 'https://example.com', 'scheme' => 'https', 'host' => 'example.com', 'path' => '/', 'query' => '', 'fragment' => '']);
    $value = $cast->get($model, 'url', $json, []);

    expect($value)->toBeInstanceOf(Url::class)
        ->and((string) $value)->toBe('https://example.com');
});

test('value object cast reconstructs Money with type-based matching (#467)', function (): void {
    $cast = new ValueObjectCast(Money::class);
    $model = new TestModelWithCast;

    $value = $cast->get($model, 'price', '{"amount":5000,"currency":"EUR"}', []);

    expect($value)->toBeInstanceOf(Money::class)
        ->and($value->amount)->toBe(5000)
        ->and($value->currency)->toBe('EUR');
});
