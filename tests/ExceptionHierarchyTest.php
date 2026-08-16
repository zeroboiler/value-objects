<?php
/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects\Tests;

use ZeroBoiler\ValueObjects\Exceptions\ValueObjectsException;
use ZeroBoiler\ValueObjects\Exceptions\InvalidValueObjectsArgumentException;
use ZeroBoiler\ValueObjects\Exceptions\ValueObjectsRuntimeException;
use Exception;

test('value-objects exception hierarchy extends Exception', function (): void {
    $exception = new class('test') extends ValueObjectsException {};

    expect($exception)
        ->toBeInstanceOf(Exception::class)
        ->toBeInstanceOf(ValueObjectsException::class)
        ->and($exception->getMessage())->toBe('test');
});

test('InvalidValueObjectsArgumentException extends ValueObjectsException', function (): void {
    $exception = new InvalidValueObjectsArgumentException('bad value');

    expect($exception)
        ->toBeInstanceOf(ValueObjectsException::class)
        ->toBeInstanceOf(Exception::class)
        ->and($exception->getMessage())->toBe('bad value');
});

test('ValueObjectsRuntimeException extends ValueObjectsException', function (): void {
    $exception = new ValueObjectsRuntimeException('operation failed');

    expect($exception)
        ->toBeInstanceOf(ValueObjectsException::class)
        ->toBeInstanceOf(Exception::class)
        ->and($exception->getMessage())->toBe('operation failed');
});

test('value-objects exceptions catchable by base type', function (): void {
    $caught = false;
    try {
        throw new ValueObjectsRuntimeException('catch me');
    } catch (ValueObjectsException $e) {
        $caught = true;
        expect($e->getMessage())->toBe('catch me');
    }

    expect($caught)->toBeTrue();
});

test('InvalidValueObjectsArgumentException has correct namespace', function (): void {
    $exception = new InvalidValueObjectsArgumentException('test');

    expect($exception::class)
        ->toBe('ZeroBoiler\\ValueObjects\\Exceptions\\InvalidValueObjectsArgumentException');
});

test('ValueObjectsRuntimeException has correct namespace', function (): void {
    $exception = new ValueObjectsRuntimeException('test');

    expect($exception::class)
        ->toBe('ZeroBoiler\\ValueObjects\\Exceptions\\ValueObjectsRuntimeException');
});
