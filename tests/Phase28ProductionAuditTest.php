<?php

/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

use ZeroBoiler\ValueObjects\Address;
use ZeroBoiler\ValueObjects\Castable;
use ZeroBoiler\ValueObjects\CastableAs;
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
use ZeroBoiler\ValueObjects\ValueObject;
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

// ─── No TODO/FIXME ───────────────────────────────────────────────────────

test('no TODO or FIXME markers in source code', function (): void {
    $srcDir = __DIR__.'/../src';
    $content = '';
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($srcDir, RecursiveDirectoryIterator::SKIP_DOTS),
    );
    foreach ($files as $file) {
        if ($file->getExtension() === 'php') {
            $c = file_get_contents($file->getRealPath());
            if ($c !== false) { $content .= $c."\n"; }
        }
    }
    expect($content)->not->toContain('TODO');
    expect($content)->not->toContain('FIXME');
});

// ─── Exception Hierarchy ──────────────────────────────────────────────────

test('exception hierarchy is correct', function (): void {
    expect((new ReflectionClass(ValueObjectsException::class))->isAbstract())->toBeTrue();
    expect((new ReflectionClass(InvalidValueObjectsArgumentException::class))->isSubclassOf(ValueObjectsException::class))->toBeTrue();
    expect((new ReflectionClass(ValueObjectsRuntimeException::class))->isSubclassOf(ValueObjectsException::class))->toBeTrue();
});

test('leaf exceptions are final', function (): void {
    expect((new ReflectionClass(InvalidValueObjectsArgumentException::class))->isFinal())->toBeTrue();
    expect((new ReflectionClass(ValueObjectsRuntimeException::class))->isFinal())->toBeTrue();
});

// ─── Value Object Classes ──────────────────────────────────────────────────

test('value object classes implement ValueObjectInterface', function (): void {
    $vos = [
        Email::class,
        PhoneNumber::class,
        Url::class,
        Money::class,
        Percentage::class,
        Currency::class,
        Duration::class,
        Address::class,
        Coordinates::class,
    ];
    foreach ($vos as $class) {
        expect((new ReflectionClass($class))->implementsInterface(ValueObjectInterface::class))
            ->toBeTrue("{$class} should implement ValueObjectInterface");
    }
});

// ─── Attributes ──────────────────────────────────────────────────────────

test('CastableAs attribute exists', function (): void {
    $ref = new ReflectionClass(CastableAs::class);
    expect($ref->isReadOnly())->toBeTrue();
});

// ─── Service Classes Finality ───────────────────────────────────────────────

test('service classes are final', function (): void {
    $finalClasses = [
        ExchangeRateProvider::class,
        ValueObjectsServiceProvider::class,
        ValueObjectCast::class,
    ];
    foreach ($finalClasses as $class) {
        expect((new ReflectionClass($class))->isFinal())->toBeTrue("{$class} should be final");
    }
});

// ─── Composer.json Integrity ────────────────────────────────────────────

test('composer.json has correct namespace and PHP requirement', function (): void {
    $composer = json_decode(file_get_contents(__DIR__.'/../composer.json'), true);
    expect($composer['autoload']['psr-4']['ZeroBoiler\\ValueObjects\\'])->toBe('src/');
    expect($composer['require']['php'])->toBe('^8.5');
    expect($composer['type'])->toBe('library');
    expect($composer['license'])->toBe('MIT');
    expect($composer['version'])->toMatch('/^\d+\.\d+\.\d+$/');
});

// ─── Version Consistency ────────────────────────────────────────────────

test('composer.json version matches README badge', function (): void {
    $composer = json_decode(file_get_contents(__DIR__.'/../composer.json'), true);
    $readme = file_get_contents(__DIR__.'/../README.md');
    expect($readme)->toContain("version-{$composer['version']}-blue");
});

// ─── Source File Count ──────────────────────────────────────────────────

test('source file count is 22', function (): void {
    $srcDir = __DIR__.'/../src';
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($srcDir, RecursiveDirectoryIterator::SKIP_DOTS),
    );
    $phpFiles = 0;
    foreach ($files as $file) {
        if ($file->getExtension() === 'php') { $phpFiles++; }
    }
    expect($phpFiles)->toBe(22, "Expected 22 source files, found {$phpFiles}");
});

// ─── Test File Count ────────────────────────────────────────────────────

test('test file count is 22', function (): void {
    $testsDir = __DIR__;
    $files = glob($testsDir.'/*Test.php');
    expect($files)->not()->toBeEmpty();
    expect(count($files))->toBe(22, "Expected 22 test files, found ".count($files));
});

// ─── phpstan.neon.dist ──────────────────────────────────────────────────

test('phpstan.neon.dist exists', function (): void {
    expect(file_exists(__DIR__.'/../phpstan.neon.dist'))->toBeTrue();
});

// ─── CHANGELOG ──────────────────────────────────────────────────────────

test('CHANGELOG.md exists', function (): void {
    expect(file_exists(__DIR__.'/../CHANGELOG.md'))->toBeTrue();
});

// ─── LICENSE ──────────────────────────────────────────────────────────────

test('MIT license file exists', function (): void {
    expect(file_exists(__DIR__.'/../LICENSE'))->toBeTrue();
});

// ─── README Sections ────────────────────────────────────────────────────

test('README contains required sections', function (): void {
    $readme = file_get_contents(__DIR__.'/../README.md');
    expect($readme)->toContain('## Installation');
    expect($readme)->toContain('## Testing');
    expect($readme)->toContain('PHP 8.5');
});
