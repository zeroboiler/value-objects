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
use ZeroBoiler\ValueObjects\ValueObject as ValueObjectBase;
use ZeroBoiler\ValueObjects\ValueObjectCast;
use ZeroBoiler\ValueObjects\ValueObjectInterface;
use ZeroBoiler\ValueObjects\ValueObjectsServiceProvider;

/**
 * Get all PHP source files from src/ directory recursively.
 *
 * @return list<string>
 */
function srcFiles(): array
{
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(__DIR__.'/../src', RecursiveDirectoryIterator::SKIP_DOTS),
    );
    foreach ($iterator as $file) {
        if ($file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }

    return $files;
}

// ═══════════════════════════════════════════════════════════════════════════════
// Phase 32 — Comprehensive Production Audit
// ═══════════════════════════════════════════════════════════════════════════════

// ─── Source File Count ─────────────────────────────────────────────────────────

test('22 source files in src/', function (): void {
    expect(count(srcFiles()))->toBe(22);
});

// ─── Strict Types ─────────────────────────────────────────────────────────────

test('all source files declare strict_types=1', function (): void {
    $srcFiles = srcFiles();
    $violations = [];
    foreach ($srcFiles as $file) {
        $content = file_get_contents($file);
        if (! str_contains($content, 'declare(strict_types=1)')) {
            $violations[] = basename($file);
        }
    }
    expect($violations)->toBeEmpty('Missing strict_types: '.implode(', ', $violations));
});

// ─── License Header ─────────────────────────────────────────────────────────

test('all source files have MIT license header', function (): void {
    $srcFiles = srcFiles();
    foreach ($srcFiles as $file) {
        $content = file_get_contents($file);
        expect($content)->toContain('This file is part of ZeroBoiler, licensed under the MIT license.');
    }
});

// ─── @since Annotations ──────────────────────────────────────────────────────

test('all public classes have @since annotation', function (): void {
    $srcFiles = srcFiles();
    $missing = [];
    foreach ($srcFiles as $file) {
        $content = file_get_contents($file);
        if (str_contains($content, "\ninterface ")) {
            continue;
        }
        if (preg_match('/\n(final\s+)?(readonly\s+)?(abstract\s+)?(class|trait)\s+\w+/', $content)) {
            if (! str_contains($content, '@since')) {
                $missing[] = basename($file);
            }
        }
    }
    expect($missing)->toBeEmpty('Missing @since: '.implode(', ', $missing));
});

// ─── Exception Hierarchy ─────────────────────────────────────────────────────

test('ValueObjectsException is abstract', function (): void {
    expect((new ReflectionClass(ValueObjectsException::class))->isAbstract())->toBeTrue();
});

test('ValueObjectsException constructor accepts ?Throwable previous', function (): void {
    $ctor = (new ReflectionClass(ValueObjectsException::class))->getConstructor();
    $params = $ctor->getParameters();
    $previous = $params[2] ?? null;
    expect($previous)->not->toBeNull();
    expect($previous->getName())->toBe('previous');
});

test('all leaf exceptions extend ValueObjectsException and are final', function (): void {
    $leaves = [InvalidValueObjectsArgumentException::class, ValueObjectsRuntimeException::class];
    foreach ($leaves as $class) {
        expect((new ReflectionClass($class))->isSubclassOf(ValueObjectsException::class))->toBeTrue();
        expect((new ReflectionClass($class))->isFinal())->toBeTrue("{$class} should be final");
    }
});

// ─── Contract Interface ─────────────────────────────────────────────────────

test('ValueObject contract has 4 required methods', function (): void {
    $ref = new ReflectionClass(ValueObject::class);
    $names = array_map(fn (ReflectionMethod $m): string => $m->getName(), $ref->getMethods());
    expect($names)->toContain('toPrimitive');
    expect($names)->toContain('fromPrimitive');
    expect($names)->toContain('equals');
    expect($names)->toContain('columnType');
});

test('ValueObject contract extends Arrayable, Jsonable, JsonSerializable, Stringable', function (): void {
    $ref = new ReflectionClass(ValueObject::class);
    $interfaces = array_map(fn (ReflectionClass $i): string => $i->getShortName(), $ref->getInterfaces());
    expect($interfaces)->toContain('Arrayable');
    expect($interfaces)->toContain('Jsonable');
    expect($interfaces)->toContain('JsonSerializable');
    expect($interfaces)->toContain('Stringable');
});

// ─── ValueObjectInterface Legacy ──────────────────────────────────────────────

test('ValueObjectInterface extends ValueObject contract', function (): void {
    expect((new ReflectionClass(ValueObjectInterface::class))->isSubclassOf(ValueObject::class))->toBeTrue();
});

// ─── ValueObject Abstract Base ────────────────────────────────────────────────

test('ValueObject base class is abstract', function (): void {
    expect((new ReflectionClass(ValueObjectBase::class))->isAbstract())->toBeTrue();
});

test('ValueObject base implements ValueObjectInterface', function (): void {
    expect((new ReflectionClass(ValueObjectBase::class))->implementsInterface(ValueObjectInterface::class))->toBeTrue();
});

// ─── Concrete VOs Extend ValueObject ──────────────────────────────────────────

test('all 9 concrete VOs extend ValueObject', function (): void {
    $vos = [Email::class, Url::class, PhoneNumber::class, Money::class, Percentage::class, Address::class, Coordinates::class, Currency::class, Duration::class];
    foreach ($vos as $class) {
        expect((new ReflectionClass($class))->isSubclassOf(ValueObjectBase::class))->toBeTrue("{$class} should extend ValueObject");
    }
});

test('all 9 concrete VOs are final', function (): void {
    $vos = [Email::class, Url::class, PhoneNumber::class, Money::class, Percentage::class, Address::class, Coordinates::class, Currency::class, Duration::class];
    foreach ($vos as $class) {
        expect((new ReflectionClass($class))->isFinal())->toBeTrue("{$class} should be final");
    }
});

// ─── Castable/CastableAs Attributes ────────────────────────────────────────

test('Castable attribute exists', function (): void {
    expect(class_exists(Castable::class))->toBeTrue();
});

test('CastableAs attribute exists', function (): void {
    expect(class_exists(CastableAs::class))->toBeTrue();
});

// ─── ValueObjectCast ────────────────────────────────────────────────────────

test('ValueObjectCast is final', function (): void {
    expect((new ReflectionClass(ValueObjectCast::class))->isFinal())->toBeTrue();
});

// ─── ServiceProvider ──────────────────────────────────────────────────────────

test('ValueObjectsServiceProvider is final', function (): void {
    expect((new ReflectionClass(ValueObjectsServiceProvider::class))->isFinal())->toBeTrue();
});

test('ServiceProvider register/boot/provides have #[Override]', function (): void {
    $ref = new ReflectionClass(ValueObjectsServiceProvider::class);
    foreach (['register', 'boot', 'provides'] as $method) {
        $attrs = $ref->getMethod($method)->getAttributes();
        $hasOverride = array_any($attrs, fn (ReflectionAttribute $a): bool => $a->getName() === 'Override');
        expect($hasOverride)->toBeTrue("ValueObjectsServiceProvider::{$method}() missing #[Override]");
    }
});

// ─── Console Commands ────────────────────────────────────────────────────────

test('all 2 console commands are final', function (): void {
    $commands = [
        \ZeroBoiler\ValueObjects\Console\Commands\MakeValueObjectCommand::class,
        \ZeroBoiler\ValueObjects\Console\Commands\ListValueObjectsCommand::class,
    ];
    foreach ($commands as $class) {
        expect((new ReflectionClass($class))->isFinal())->toBeTrue("{$class} should be final");
    }
});

// ─── Composer Metadata ──────────────────────────────────────────────────────

test('composer.json PHP version is ^8.5', function (): void {
    $composer = json_decode(file_get_contents(__DIR__.'/../composer.json'), true);
    expect($composer['require']['php'])->toBe('^8.5');
});

test('composer.json has correct namespace', function (): void {
    $composer = json_decode(file_get_contents(__DIR__.'/../composer.json'), true);
    expect($composer['autoload']['psr-4'])->toHaveKey('ZeroBoiler\\ValueObjects\\');
});

test('composer.json has provider registration', function (): void {
    $composer = json_decode(file_get_contents(__DIR__.'/../composer.json'), true);
    expect($composer['extra']['laravel']['providers'])->toContain(
        'ZeroBoiler\\ValueObjects\\ValueObjectsServiceProvider',
    );
});

test('composer.json version is 1.38.0', function (): void {
    $composer = json_decode(file_get_contents(__DIR__.'/../composer.json'), true);
    expect($composer['version'])->toBe('1.38.0');
});

// ─── Version Consistency ─────────────────────────────────────────────────────

test('README version matches composer.json', function (): void {
    $composer = json_decode(file_get_contents(__DIR__.'/../composer.json'), true);
    $readme = file_get_contents(__DIR__.'/../README.md');
    expect($readme)->toContain("version-{$composer['version']}");
});

// ─── Zero TODO/FIXME ──────────────────────────────────────────────────────────

test('no TODO/FIXME in source', function (): void {
    $srcFiles = srcFiles();
    $violations = [];
    foreach ($srcFiles as $file) {
        $content = file_get_contents($file);
        if (preg_match('/\b(TODO|FIXME|HACK|XXX)\b/', $content, $m)) {
            $violations[] = basename($file).':'.$m[0];
        }
    }
    expect($violations)->toBeEmpty('Found: '.implode(', ', $violations));
});

// ─── ExchangeRateProvider Interface ──────────────────────────────────────────

test('ExchangeRateProvider is an interface', function (): void {
    $ref = new ReflectionClass(ExchangeRateProvider::class);
    expect($ref->isInterface())->toBeTrue();
    expect($ref->hasMethod('getRate'))->toBeTrue();
});

// ─── Constructor :void Return Types ──────────────────────────────────────────

test('all concrete VOs have constructor with :void return type', function (): void {
    $vos = [Email::class, Url::class, PhoneNumber::class, Money::class, Percentage::class, Address::class, Coordinates::class, Currency::class, Duration::class];
    foreach ($vos as $class) {
        $ctor = (new ReflectionClass($class))->getConstructor();
        expect($ctor)->not->toBeNull("{$class} should have a constructor");
        expect($ctor->getReturnType()?->getName())->toBe('void', "{$class} constructor should return void");
    }
});

test('ValueObjectCast constructor has :void return type', function (): void {
    $ctor = (new ReflectionClass(ValueObjectCast::class))->getConstructor();
    expect($ctor)->not->toBeNull();
    expect($ctor->getReturnType()?->getName())->toBe('void');
});

// ─── CastableAs Readonly ────────────────────────────────────────────────────

test('CastableAs attribute is readonly', function (): void {
    $ref = new ReflectionClass(CastableAs::class);
    expect($ref->isReadOnly())->toBeTrue();
});

test('CastableAs attribute targets CLASS', function (): void {
    $attrs = (new ReflectionClass(CastableAs::class))->getAttributes();
    $hasTarget = array_any($attrs, fn (ReflectionAttribute $a): bool => $a->getName() === 'Attribute');
    expect($hasTarget)->toBeTrue();
});

// ─── VOs implement ValueObject contract ────────────────────────────────────

test('all 9 concrete VOs implement ValueObject contract', function (): void {
    $vos = [Email::class, Url::class, PhoneNumber::class, Money::class, Percentage::class, Address::class, Coordinates::class, Currency::class, Duration::class];
    foreach ($vos as $class) {
        expect((new ReflectionClass($class))->implementsInterface(ValueObject::class))->toBeTrue("{$class} should implement ValueObject");
    }
});
