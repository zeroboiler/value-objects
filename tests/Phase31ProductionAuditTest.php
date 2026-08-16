<?php
/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

use ZeroBoiler\ValueObjects\Exceptions\InvalidValueObjectsArgumentException;
use ZeroBoiler\ValueObjects\Exceptions\ValueObjectsException;
use ZeroBoiler\ValueObjects\Exceptions\ValueObjectsRuntimeException;

// ═══════════════════════════════════════════════════════════════════════════════
// Phase 31 — Deep Production Readiness Audit
// ═══════════════════════════════════════════════════════════════════════════════

test('all 22 source files declare strict_types=1', function (): void {
    $srcFiles = glob(__DIR__.'/../src/**/*.php');
    expect(is_array($srcFiles) ? count($srcFiles) : 0)->toBe(22);

    $violations = [];
    foreach ($srcFiles as $file) {
        if (! str_contains(file_get_contents($file), 'declare(strict_types=1)')) {
            $violations[] = basename($file);
        }
    }
    expect($violations)->toBeEmpty();
});

test('all source files have MIT license header', function (): void {
    foreach (glob(__DIR__.'/../src/**/*.php') as $file) {
        expect(file_get_contents($file))->toContain('This file is part of ZeroBoiler, licensed under the MIT license.');
    }
});

test('no TODO/FIXME/HACK markers in source files', function (): void {
    $violations = [];
    foreach (glob(__DIR__.'/../src/**/*.php') as $file) {
        if (preg_match('/\b(TODO|FIXME|HACK|XXX)\b/', file_get_contents($file), $m)) {
            $violations[] = basename($file).':'.$m[0];
        }
    }
    expect($violations)->toBeEmpty();
});

test('ValueObjectsException is abstract base', function (): void {
    $ref = new ReflectionClass(ValueObjectsException::class);
    expect($ref->isAbstract())->toBeTrue();
    expect($ref->isFinal())->toBeFalse();
});

test('leaf exceptions extend ValueObjectsException and are final', function (): void {
    foreach ([InvalidValueObjectsArgumentException::class, ValueObjectsRuntimeException::class] as $class) {
        expect((new ReflectionClass($class))->isSubclassOf(ValueObjectsException::class))->toBeTrue();
        expect((new ReflectionClass($class))->isFinal())->toBeTrue();
    }
});

test('ValueObjectsException constructor accepts Throwable for previous', function (): void {
    $params = (new ReflectionClass(ValueObjectsException::class))->getConstructor()->getParameters();
    $prev = $params[2];
    expect($prev->getName())->toBe('previous');
    expect($prev->getType()?->getName() === 'Throwable' || $prev->getType()?->allowsNull() === true)->toBeTrue();
});

test('composer.json has PHP ^8.5 and correct namespace', function (): void {
    $c = json_decode(file_get_contents(__DIR__.'/../composer.json'), true);
    expect($c['require']['php'])->toBe('^8.5');
    expect($c['autoload']['psr-4']['ZeroBoiler\\ValueObjects\\'])->toBe('src/');
    expect($c['version'])->not->toBeNull();
});
