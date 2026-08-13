<?php

/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects\Tests;

use ZeroBoiler\ValueObjects\Exceptions\Value-objectsException;
use ZeroBoiler\ValueObjects\Exceptions\InvalidValue-objectsArgumentException;
use ZeroBoiler\ValueObjects\Exceptions\Value-objectsRuntimeException;
use Exception;

test('value-objects exception hierarchy extends Exception', function (): void {
    $exception = new class('test') extends Value-objectsException {};

    expect($exception)
        ->toBeInstanceOf(Exception::class)
        ->toBeInstanceOf(Value-objectsException::class)
        ->and($exception->getMessage())->toBe('test');
});

test('InvalidValue-objectsArgumentException extends Value-objectsException', function (): void {
    $exception = new InvalidValue-objectsArgumentException('bad value');

    expect($exception)
        ->toBeInstanceOf(Value-objectsException::class)
        ->toBeInstanceOf(Exception::class)
        ->and($exception->getMessage())->toBe('bad value');
});

test('Value-objectsRuntimeException extends Value-objectsException', function (): void {
    $exception = new Value-objectsRuntimeException('operation failed');

    expect($exception)
        ->toBeInstanceOf(Value-objectsException::class)
        ->toBeInstanceOf(Exception::class)
        ->and($exception->getMessage())->toBe('operation failed');
});

test('value-objects exceptions catchable by base type', function (): void {
    $caught = false;
    try {
        throw new Value-objectsRuntimeException('catch me');
    } catch (Value-objectsException $e) {
        $caught = true;
        expect($e->getMessage())->toBe('catch me');
    }

    expect($caught)->toBeTrue();
});
