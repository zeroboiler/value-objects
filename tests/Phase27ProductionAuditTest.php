<?php
/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

use ZeroBoiler\ValueObjects\Address;
use ZeroBoiler\ValueObjects\Castable;
use ZeroBoiler\ValueObjects\CastableAs;
use ZeroBoiler\ValueObjects\Contracts\ValueObject;
use ZeroBoiler\ValueObjects\Coordinates;
use ZeroBoiler\ValueObjects\Currency;
use ZeroBoiler\ValueObjects\Duration;
use ZeroBoiler\ValueObjects\Email;
use ZeroBoiler\ValueObjects\Exceptions\InvalidValueObjectsArgumentException;
use ZeroBoiler\ValueObjects\Exceptions\ValueObjectsException;
use ZeroBoiler\ValueObjects\Exceptions\ValueObjectsRuntimeException;
use ZeroBoiler\ValueObjects\ExchangeRateProvider;
use ZeroBoiler\ValueObjects\Money;
use ZeroBoiler\ValueObjects\Percentage;
use ZeroBoiler\ValueObjects\PhoneNumber;
use ZeroBoiler\ValueObjects\Url;
use ZeroBoiler\ValueObjects\ValueObject as BaseValueObject;
use ZeroBoiler\ValueObjects\ValueObjectCast;
use ZeroBoiler\ValueObjects\ValueObjectInterface;
use ZeroBoiler\ValueObjects\ValueObjectsServiceProvider;

// ─── Source File Strict Types Verification ─────────────────────────────────

test('all source files declare strict_types=1', function (): void {
    $srcDir = __DIR__.'/../src';
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($srcDir, RecursiveDirectoryIterator::SKIP_DOTS),
    );
    $violations = [];
    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }
        $content = file_get_contents($file->getRealPath());
        if ($content === false || ! str_contains($content, 'declare(strict_types=1)')) {
            $violations[] = $file->getFilename();
        }
    }
    expect($violations)->toBeEmpty('Files missing declare(strict_types=1): '.implode(', ', $violations));
});

// ─── No TODO/FIXME Markers ───────────────────────────────────────────────────

test('no TODO or FIXME markers in source code', function (): void {
    $srcDir = __DIR__.'/../src';
    $content = '';
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($srcDir, RecursiveDirectoryIterator::SKIP_DOTS),
    );
    foreach ($files as $file) {
        if ($file->getExtension() === 'php') {
            $c = file_get_contents($file->getRealPath());
            if ($c !== false) {
                $content .= $c."\n";
            }
        }
    }
    expect($content)->not->toContain('TODO');
    expect($content)->not->toContain('FIXME');
});

// ─── Exception Hierarchy ─────────────────────────────────────────────────────

test('exception hierarchy is correct', function (): void {
    expect((new ReflectionClass(ValueObjectsException::class))->isAbstract())->toBeTrue();
    expect((new ReflectionClass(InvalidValueObjectsArgumentException::class))->isSubclassOf(ValueObjectsException::class))->toBeTrue();
    expect((new ReflectionClass(ValueObjectsRuntimeException::class))->isSubclassOf(ValueObjectsException::class))->toBeTrue();
});

test('leaf exceptions are final', function (): void {
    $finalLeafs = [
        InvalidValueObjectsArgumentException::class,
        ValueObjectsRuntimeException::class,
    ];
    foreach ($finalLeafs as $class) {
        expect((new ReflectionClass($class))->isFinal())->toBeTrue("{$class} should be final");
    }
});

// ─── Value Object Classes Extend Base ───────────────────────────────────────

test('all value object classes extend ValueObject base', function (): void {
    $valueObjects = [
        Address::class,
        Coordinates::class,
        Currency::class,
        Duration::class,
        Email::class,
        Money::class,
        Percentage::class,
        PhoneNumber::class,
        Url::class,
    ];

    foreach ($valueObjects as $class) {
        expect((new ReflectionClass($class))->isSubclassOf(BaseValueObject::class))
            ->toBeTrue("{$class} should extend ValueObject");
    }
});

// ─── Value Objects are Readonly ───────────────────────────────────────────────

test('value object classes are readonly', function (): void {
    $valueObjects = [
        Address::class,
        Coordinates::class,
        Currency::class,
        Duration::class,
        Email::class,
        Money::class,
        Percentage::class,
        PhoneNumber::class,
        Url::class,
    ];

    foreach ($valueObjects as $class) {
        expect((new ReflectionClass($class))->isReadOnly())->toBeTrue("{$class} should be readonly");
    }
});

// ─── Interfaces ─────────────────────────────────────────────────────────────

test('ValueObject interface exists', function (): void {
    expect((new ReflectionClass(ValueObject::class))->isInterface())->toBeTrue();
});

test('ValueObjectInterface exists and matches contract', function (): void {
    expect((new ReflectionClass(ValueObjectInterface::class))->isInterface())->toBeTrue();
    expect((new ReflectionClass(ValueObjectInterface::class))->hasMethod('toArray'))->toBeTrue();
    expect((new ReflectionClass(ValueObjectInterface::class))->hasMethod('jsonSerialize'))->toBeTrue();
});

// ─── Attributes ──────────────────────────────────────────────────────────────

test('Castable and CastableAs are attributes', function (): void {
    expect((new ReflectionClass(Castable::class))->isAttribute())->toBeTrue();
    expect((new ReflectionClass(CastableAs::class))->isAttribute())->toBeTrue();
});

// ─── Service Class Finality ─────────────────────────────────────────────────

test('service classes are final', function (): void {
    $finalClasses = [
        ValueObjectCast::class,
        ExchangeRateProvider::class,
        ValueObjectsServiceProvider::class,
    ];

    foreach ($finalClasses as $class) {
        expect((new ReflectionClass($class))->isFinal())->toBeTrue("{$class} should be final");
    }
});

// ─── Composer.json Integrity ───────────────────────────────────────────────

test('composer.json has correct structure', function (): void {
    $composer = json_decode(file_get_contents(__DIR__.'/../composer.json'), true);
    expect($composer['autoload']['psr-4']['ZeroBoiler\\ValueObjects\\'])->toBe('src/');
    expect($composer['require']['php'])->toBe('^8.5');
    expect($composer['type'])->toBe('library');
    expect($composer['license'])->toBe('MIT');
    expect($composer['version'])->toMatch('/^\d+\.\d+\.\d+$/');
});

// ─── Version Consistency ──────────────────────────────────────────────────

test('composer.json version matches README badge', function (): void {
    $composer = json_decode(file_get_contents(__DIR__.'/../composer.json'), true);
    $readme = file_get_contents(__DIR__.'/../README.md');
    expect($readme)->toContain("version-{$composer['version']}-blue");
});
