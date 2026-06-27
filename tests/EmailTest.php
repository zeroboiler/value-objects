<?php

/**
 * This file is part of ZeroBoiler, licensed under the proprietary license.
 */

declare(strict_types=1);

use Illuminate\Validation\ValidationException;
use ZeroBoiler\ValueObjects\Email;

test('email can be created', function (): void {
    $email = new Email('test@example.com');

    expect($email->value)->toBe('test@example.com');
});

test('email is normalized to lowercase', function (): void {
    $email = new Email('TEST@EXAMPLE.COM');

    expect($email->value)->toBe('test@example.com');
});

test('email is trimmed', function (): void {
    $email = new Email('  test@example.com  ');

    expect($email->value)->toBe('test@example.com');
});

test('invalid email throws validation exception', function (): void {
    expect(fn (): Email => new Email('invalid-email'))->toThrow(ValidationException::class);
});

test('email can extract domain', function (): void {
    $email = new Email('test@example.com');

    expect($email->domain())->toBe('example.com');
});

test('email can extract local part', function (): void {
    $email = new Email('test@example.com');

    expect($email->localPart())->toBe('test');
});

test('email equals compares by value', function (): void {
    $email1 = new Email('test@example.com');
    $email2 = new Email('TEST@example.com');
    $email3 = new Email('other@example.com');

    expect($email1->equals($email2))->toBeTrue()
        ->and($email1->equals($email3))->toBeFalse();
});

test('email can be converted to string', function (): void {
    $email = new Email('test@example.com');

    expect((string) $email)->toBe('test@example.com');
});

test('email can be serialized', function (): void {
    $email = new Email('test@example.com');

    expect($email->toArray())->toBe(['email' => 'test@example.com']);
});
