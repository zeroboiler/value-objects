<?php

use ZeroBoiler\ValueObjects\ValueObjects\Address;

test('address can be created', function () {
    $address = new Address(
        '123 Main St',
        'Apt 4B',
        'Springfield',
        'IL',
        '62701',
        'USA'
    );

    expect($address->street)->toBe('123 Main St')
        ->and($address->street2)->toBe('Apt 4B')
        ->and($address->city)->toBe('Springfield')
        ->and($address->state)->toBe('IL')
        ->and($address->postalCode)->toBe('62701')
        ->and($address->country)->toBe('USA');
});

test('address can be created without street2', function () {
    $address = new Address(
        '123 Main St',
        null,
        'Springfield',
        'IL',
        '62701',
        'USA'
    );

    expect($address->street2)->toBeNull();
});

test('address fields are trimmed', function () {
    $address = new Address(
        '  123 Main St  ',
        '  Apt 4B  ',
        '  Springfield  ',
        '  IL  ',
        '  62701  ',
        '  USA  '
    );

    expect($address->street)->toBe('123 Main St')
        ->and($address->city)->toBe('Springfield');
});

test('address can get full address', function () {
    $address = new Address(
        '123 Main St',
        'Apt 4B',
        'Springfield',
        'IL',
        '62701',
        'USA'
    );

    $full = $address->full();

    expect($full)->toContain('123 Main St')
        ->toContain('Springfield')
        ->toContain('IL')
        ->toContain('62701');
});

test('address can get lines array', function () {
    $address = new Address(
        '123 Main St',
        'Apt 4B',
        'Springfield',
        'IL',
        '62701',
        'USA'
    );

    $lines = $address->lines();

    expect($lines)->toBeArray()
        ->and($lines[0])->toBe('123 Main St')
        ->and($lines[1])->toBe('Apt 4B')
        ->and($lines[2])->toBe('Springfield, IL 62701')
        ->and($lines[3])->toBe('USA');
});

test('address lines filters null values', function () {
    $address = new Address(
        '123 Main St',
        null,
        'Springfield',
        'IL',
        '62701',
        'USA'
    );

    $lines = $address->lines();

    expect($lines)->not->toContain(null);
});

test('address equals compares by value', function () {
    $address1 = new Address(
        '123 Main St',
        null,
        'Springfield',
        'IL',
        '62701',
        'USA'
    );
    $address2 = new Address(
        '123 Main St',
        null,
        'Springfield',
        'IL',
        '62701',
        'USA'
    );
    $address3 = new Address(
        '456 Oak Ave',
        null,
        'Springfield',
        'IL',
        '62701',
        'USA'
    );

    expect($address1->equals($address2))->toBeTrue()
        ->and($address1->equals($address3))->toBeFalse();
});

test('address can be converted to string', function () {
    $address = new Address(
        '123 Main St',
        null,
        'Springfield',
        'IL',
        '62701',
        'USA'
    );

    expect((string) $address)->toContain('123 Main St');
});

test('address can be serialized', function () {
    $address = new Address(
        '123 Main St',
        null,
        'Springfield',
        'IL',
        '62701',
        'USA'
    );

    expect($address->toArray())->toBe([
        'street' => '123 Main St',
        'street2' => null,
        'city' => 'Springfield',
        'state' => 'IL',
        'postalCode' => '62701',
        'country' => 'USA',
    ]);
});