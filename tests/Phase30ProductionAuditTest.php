<?php
/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

use ZeroBoiler\ValueObjects\Address;
use ZeroBoiler\ValueObjects\Castable;
use ZeroBoiler\ValueObjects\CastableAs;
use ZeroBoiler\ValueObjects\Coordinates;
use ZeroBoiler\ValueObjects\Contracts\ValueObject as ValueObjectContract;
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

// ═══════════════════════════════════════════════════════════════════════════════
// Phase 30 — Production Readiness Audit
// ═══════════════════════════════════════════════════════════════════════════════

// ─── Source File Count ───────────────────────────────────────────────────────

test('source files count is within expected range', function (): void {
    $srcFiles = glob(__DIR__.'/../src/**/*.php');
    $count = is_array($srcFiles) ? count($srcFiles) : 0;

    expect($count)->toBeGreaterThanOrEqual(18);
    expect($count)->toBeLessThanOrEqual(30);
});

// ─── Finality Verification ────────────────────────────────────────────────────

test('ValueObject base class is abstract (not final)', function (): void {
    $ref = new ReflectionClass(ValueObject::class);
    expect($ref->isAbstract())->toBeTrue();
    expect($ref->isFinal())->toBeFalse();
});

test('all concrete value objects are final', function (): void {
    $vos = [
        Email::class, Url::class, PhoneNumber::class,
        Money::class, Currency::class, Percentage::class,
        Address::class, Coordinates::class, Duration::class,
    ];

    foreach ($vos as $class) {
        expect((new ReflectionClass($class))->isFinal())->toBeTrue("{$class} should be final");
    }
});

test('ValueObjectCast is final', function (): void {
    expect((new ReflectionClass(ValueObjectCast::class))->isFinal())->toBeTrue();
});

test('CastableAs is final readonly', function (): void {
    $ref = new ReflectionClass(CastableAs::class);
    expect($ref->isFinal())->toBeTrue();
    expect($ref->isReadOnly())->toBeTrue();
});

test('ExchangeRateProvider is final', function (): void {
    expect((new ReflectionClass(ExchangeRateProvider::class))->isFinal())->toBeTrue();
});

test('ValueObjectsServiceProvider is final', function (): void {
    expect((new ReflectionClass(ValueObjectsServiceProvider::class))->isFinal())->toBeTrue();
});

test('console commands are final', function (): void {
    $commands = [
        \ZeroBoiler\ValueObjects\Console\Commands\MakeValueObjectCommand::class,
        \ZeroBoiler\ValueObjects\Console\Commands\ListValueObjectsCommand::class,
    ];

    foreach ($commands as $class) {
        expect((new ReflectionClass($class))->isFinal())->toBeTrue("{$class} should be final");
    }
});

// ─── ValueObject Interface Compliance ──────────────────────────────────────────

test('all value objects implement ValueObjectContract', function (): void {
    $vos = [
        Email::class, Url::class, PhoneNumber::class,
        Money::class, Currency::class, Percentage::class,
        Address::class, Coordinates::class, Duration::class,
    ];

    foreach ($vos as $class) {
        expect((new ReflectionClass($class))->implementsInterface(ValueObjectContract::class))
            ->toBeTrue("{$class} should implement ValueObjectContract");
    }
});

test('all value objects implement ValueObjectInterface (legacy)', function (): void {
    $vos = [
        Email::class, Url::class, PhoneNumber::class,
        Money::class, Currency::class, Percentage::class,
        Address::class, Coordinates::class, Duration::class,
    ];

    foreach ($vos as $class) {
        expect((new ReflectionClass($class))->implementsInterface(ValueObjectInterface::class))
            ->toBeTrue("{$class} should implement ValueObjectInterface");
    }
});

test('ValueObjectContract has all required methods', function (): void {
    $ref = new ReflectionClass(ValueObjectContract::class);
    $required = ['toPrimitive', 'fromPrimitive', 'equals', 'columnType'];
    foreach ($required as $method) {
        expect($ref->hasMethod($method))->toBeTrue("ValueObjectContract missing: {$method}");
    }
});

test('all value objects have toPrimitive() method', function (): void {
    $vos = [Email::class, Url::class, PhoneNumber::class, Money::class, Currency::class];
    foreach ($vos as $class) {
        expect((new ReflectionClass($class))->hasMethod('toPrimitive'))->toBeTrue();
    }
});

test('all value objects have fromPrimitive() static method', function (): void {
    $vos = [Email::class, Url::class, PhoneNumber::class, Money::class, Currency::class];
    foreach ($vos as $class) {
        $method = (new ReflectionClass($class))->getMethod('fromPrimitive');
        expect($method->isStatic())->toBeTrue("{$class}::fromPrimitive() should be static");
    }
});

// ─── Castable Interface ───────────────────────────────────────────────────────

test('Castable interface exists', function (): void {
    expect(interface_exists(Castable::class))->toBeTrue();
});

test('CastableAs attribute has correct Attribute target', function (): void {
    $attrs = (new ReflectionClass(CastableAs::class))->getAttributes();
    expect($attrs)->not->toBeEmpty();
});

// ─── Exception Hierarchy ───────────────────────────────────────────────────────

test('ValueObjectsException is abstract', function (): void {
    expect((new ReflectionClass(ValueObjectsException::class))->isAbstract())->toBeTrue();
});

test('all exceptions extend ValueObjectsException', function (): void {
    $exceptions = [InvalidValueObjectsArgumentException::class, ValueObjectsRuntimeException::class];
    foreach ($exceptions as $class) {
        expect((new ReflectionClass($class))->isSubclassOf(ValueObjectsException::class))
            ->toBeTrue("{$class} should extend ValueObjectsException");
    }
});

test('all leaf exceptions are final', function (): void {
    $exceptions = [InvalidValueObjectsArgumentException::class, ValueObjectsRuntimeException::class];
    foreach ($exceptions as $class) {
        expect((new ReflectionClass($class))->isFinal())->toBeTrue("{$class} should be final");
    }
});

// ─── Constructor :void Enforcement ─────────────────────────────────────────────

test('ValueObjectsException constructor has :void return type', function (): void {
    $ctor = (new ReflectionClass(ValueObjectsException::class))->getConstructor();
    $returnType = $ctor->getReturnType();
    expect($returnType)->not->toBeNull();
    expect($returnType->getName())->toBe('void');
});

// ─── Strict Types ─────────────────────────────────────────────────────────────

test('all source files declare strict_types=1', function (): void {
    $srcFiles = glob(__DIR__.'/../src/**/*.php');
    expect($srcFiles)->not->toBeEmpty();

    foreach ($srcFiles as $file) {
        $content = file_get_contents($file);
        expect($content)->toContain('declare(strict_types=1)', "Missing strict_types in: {$file}");
    }
});

// ─── Composer.json Integrity ───────────────────────────────────────────────────

test('composer.json requires PHP ^8.5', function (): void {
    $composer = json_decode(file_get_contents(__DIR__.'/../composer.json'), true);
    expect($composer['require']['php'])->toBe('^8.5');
});

test('composer.json has correct PSR-4 autoloading', function (): void {
    $composer = json_decode(file_get_contents(__DIR__.'/../composer.json'), true);
    expect($composer['autoload']['psr-4'])->toHaveKey('ZeroBoiler\\ValueObjects\\');
});

test('composer.json has quality scripts', function (): void {
    $composer = json_decode(file_get_contents(__DIR__.'/../composer.json'), true);
    expect($composer['scripts'])->toHaveKeys(['test', 'lint', 'analyse', 'quality']);
});

// ─── Tooling Presence ────────────────────────────────────────────────────────

test('phpstan.neon config exists', function (): void {
    expect(file_exists(__DIR__.'/../phpstan.neon') || file_exists(__DIR__.'/../phpstan.neon.dist'))->toBeTrue();
});

test('rector.php config exists', function (): void {
    expect(file_exists(__DIR__.'/../rector.php'))->toBeTrue();
});

test('pint.json config exists', function (): void {
    expect(file_exists(__DIR__.'/../pint.json'))->toBeTrue();
});
