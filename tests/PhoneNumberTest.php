<?php

/**
 * This file is part of ZeroBoiler, licensed under the proprietary license.
 */

declare(strict_types=1);

use Illuminate\Validation\ValidationException;
use ZeroBoiler\ValueObjects\PhoneNumber;

test('phone number can be created', function (): void {
    $phone = new PhoneNumber('+1234567890');

    expect($phone->value)->toBe('+1234567890');
});

test('invalid phone number throws validation exception', function (): void {
    expect(fn (): PhoneNumber => new PhoneNumber('1234567890'))->toThrow(ValidationException::class);
});

test('phone number is trimmed', function (): void {
    $phone = new PhoneNumber('  +1234567890  ');

    expect($phone->value)->toBe('+1234567890');
});

test('phone number can extract country code', function (): void {
    $phone = new PhoneNumber('+1234567890');

    expect($phone->countryCode())->toBe('1');
});

test('phone number can extract longer country code', function (): void {
    $phone = new PhoneNumber('+441234567890');

    expect($phone->countryCode())->toBe('44');
});

test('phone number can be formatted for display', function (): void {
    $phone = new PhoneNumber('+1234567890');

    expect($phone->format())->toBeString();
});

test('phone number format adds spaces for readability', function (): void {
    $phone = new PhoneNumber('+15551234567');

    $formatted = $phone->format();

    expect($formatted)->toContain(' ');
});

test('phone number equals compares by value', function (): void {
    $phone1 = new PhoneNumber('+1234567890');
    $phone2 = new PhoneNumber('+1234567890');
    $phone3 = new PhoneNumber('+19876543210');

    expect($phone1->equals($phone2))->toBeTrue()
        ->and($phone1->equals($phone3))->toBeFalse();
});

test('phone number can be converted to string', function (): void {
    $phone = new PhoneNumber('+1234567890');

    expect((string) $phone)->toBe('+1234567890');
});

test('phone number can be serialized', function (): void {
    $phone = new PhoneNumber('+1234567890');

    expect($phone->toArray())->toBe(['phone' => '+1234567890']);
});
