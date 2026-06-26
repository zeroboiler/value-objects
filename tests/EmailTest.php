<?php

use ZeroBoiler\ValueObjects\ValueObjects\Email;
use Illuminate\Validation\ValidationException;

test('email can be created', function () {
    $email = new Email('test@example.com');

    expect($email->value)->toBe('test@example.com');
});

test('email is normalized to lowercase', function () {
    $email = new Email('TEST@EXAMPLE.COM');

    expect($email->value)->toBe('test@example.com');
});

test('email is trimmed', function () {
    $email = new Email('  test@example.com  ');

    expect($email->value)->toBe('test@example.com');
});

test('invalid email throws validation exception', function () {
    expect(fn () => new Email('invalid-email'))->toThrow(ValidationException::class);
});

test('email can extract domain', function () {
    $email = new Email('test@example.com');

    expect($email->domain())->toBe('example.com');
});

test('email can extract local part', function () {
    $email = new Email('test@example.com');

    expect($email->localPart())->toBe('test');
});

test('email equals compares by value', function () {
    $email1 = new Email('test@example.com');
    $email2 = new Email('TEST@example.com');
    $email3 = new Email('other@example.com');

    expect($email1->equals($email2))->toBeTrue()
        ->and($email1->equals($email3))->toBeFalse();
});

test('email can be converted to string', function () {
    $email = new Email('test@example.com');

    expect((string) $email)->toBe('test@example.com');
});

test('email can be serialized', function () {
    $email = new Email('test@example.com');

    expect($email->toArray())->toBe(['email' => 'test@example.com']);
});