<?php
/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

/**
 * Recursively glob for files matching a pattern.
 *
 * @return list<string>
 */
function vo37_glob_recursive(string $pattern, int $flags = 0): array
{
    $files = glob($pattern, $flags);

    foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
        $files = array_merge(
            $files,
            vo37_glob_recursive($dir . '/' . basename($pattern), $flags),
        );
    }

    return $files ?: [];
}

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

// ── Version consistency ─────────────────────────────────────────────

test('version is 1.43.0', function (): void {
    $json = json_decode(file_get_contents(__DIR__ . '/../composer.json'), true);
    expect($json['version'])->toBe('1.43.0');
});

// ── Source file count ────────────────────────────────────────────────

test('source file count is 22', function (): void {
    $srcDir = __DIR__ . '/../src';
    $files = vo37_glob_recursive($srcDir . '/*.php');
    expect(count($files))->toBe(22, 'Expected 22 source files');
});

// ── Exception hierarchy ──────────────────────────────────────────────

test('ValueObjectsException has @see child references', function (): void {
    $ref = new ReflectionClass(ValueObjectsException::class);
    $doc = $ref->getDocComment() ?: '';
    expect($doc)->toContain('@see InvalidValueObjectsArgumentException');
    expect($doc)->toContain('@see ValueObjectsRuntimeException');
});

test('exception hierarchy: abstract base → final leaves', function (): void {
    expect((new ReflectionClass(ValueObjectsException::class))->isAbstract())->toBeTrue();

    $leaves = [
        InvalidValueObjectsArgumentException::class,
        ValueObjectsRuntimeException::class,
    ];

    foreach ($leaves as $class) {
        expect((new ReflectionClass($class))->isFinal())->toBeTrue(
            "{$class} must be final"
        );
        expect(is_subclass_of($class, ValueObjectsException::class))->toBeTrue(
            "{$class} must extend ValueObjectsException"
        );
    }
});

// ── All 9 concrete VOs are final ────────────────────────────────────

test('all 9 concrete value objects are final', function (): void {
    $vos = [
        Address::class, Coordinates::class, Currency::class,
        Duration::class, Email::class, Money::class,
        Percentage::class, PhoneNumber::class, Url::class,
    ];

    foreach ($vos as $class) {
        expect((new ReflectionClass($class))->isFinal())->toBeTrue(
            "{$class} must be final"
        );
    }
});

// ── All VOs extend ValueObject base ─────────────────────────────────

test('all 9 concrete VOs extend ValueObject', function (): void {
    $vos = [
        Address::class, Coordinates::class, Currency::class,
        Duration::class, Email::class, Money::class,
        Percentage::class, PhoneNumber::class, Url::class,
    ];

    foreach ($vos as $class) {
        expect(is_subclass_of($class, ValueObject::class))->toBeTrue(
            "{$class} must extend " . ValueObject::class
        );
    }
});

// ── All VOs implement ValueObjectContract ────────────────────────────

test('all 9 concrete VOs implement ValueObjectContract', function (): void {
    $vos = [
        Address::class, Coordinates::class, Currency::class,
        Duration::class, Email::class, Money::class,
        Percentage::class, PhoneNumber::class, Url::class,
    ];

    foreach ($vos as $class) {
        $ref = new ReflectionClass($class);
        expect($ref->implementsInterface(ValueObjectContract::class))->toBeTrue(
            "{$class} must implement " . ValueObjectContract::class
        );
    }
});

// ── ValueObjectContract interface methods ────────────────────────────

test('ValueObjectContract defines 4 required methods', function (): void {
    $ref = new ReflectionClass(ValueObjectContract::class);
    $methods = $ref->getMethods();

    $methodNames = array_map(fn (\ReflectionMethod $m): string => $m->getName(), $methods);
    expect($methodNames)->toContain('toPrimitive');
    expect($methodNames)->toContain('fromPrimitive');
    expect($methodNames)->toContain('equals');
    expect($methodNames)->toContain('columnType');
});

// ── Service classes are final ────────────────────────────────────────

test('service classes are final', function (): void {
    $services = [
        ValueObjectCast::class,
        ValueObjectsServiceProvider::class,
    ];

    foreach ($services as $class) {
        expect((new ReflectionClass($class))->isFinal())->toBeTrue(
            "{$class} must be final"
        );
    }
});

// ── Abstract classes ────────────────────────────────────────────────

test('ValueObject is abstract', function (): void {
    expect((new ReflectionClass(ValueObject::class))->isAbstract())->toBeTrue();
});

// ── Interfaces ──────────────────────────────────────────────────────

test('ValueObjectContract is an interface', function (): void {
    expect((new ReflectionClass(ValueObjectContract::class))->isInterface())->toBeTrue();
});

test('ValueObjectInterface extends ValueObjectContract for backward compatibility', function (): void {
    expect((new ReflectionClass(ValueObjectInterface::class))->isInterface())->toBeTrue();
    expect(
        (new ReflectionClass(ValueObjectInterface::class))->isSubclassOf(ValueObjectContract::class)
    )->toBeTrue();
});

// ── ValueObject base class methods ──────────────────────────────────

test('ValueObject base has validate/toPrimitive/fromPrimitive/columnType/equals/toArray/toJson/jsonSerialize', function (): void {
    $ref = new ReflectionClass(ValueObject::class);

    $expectedMethods = [
        'validate', 'toPrimitive', 'fromPrimitive', 'columnType',
        'equals', 'toArray', 'toJson', 'jsonSerialize',
    ];

    foreach ($expectedMethods as $method) {
        expect($ref->hasMethod($method))->toBeTrue(
            "ValueObject must have method {$method}()"
        );
    }
});

// ── Constructor :void return types ──────────────────────────────────

test('ValueObjectsException constructor has :void return type', function (): void {
    $ref = new ReflectionClass(ValueObjectsException::class);
    $ctor = $ref->getConstructor();
    expect($ctor?->hasReturnType())->toBeTrue();
    expect($ctor?->getReturnType()?->getName())->toBe('void');
});

// ── ServiceProvider #[Override] audit ───────────────────────────────

test('ServiceProvider has #[Override] on register, boot, provides', function (): void {
    $ref = new ReflectionClass(ValueObjectsServiceProvider::class);

    foreach (['register', 'boot', 'provides'] as $method) {
        $m = $ref->getMethod($method);
        expect($m->getAttributes(\Override::class))->not->toBeEmpty(
            "ValueObjectsServiceProvider::{$method} must have #[Override]"
        );
    }
});

// ── CastableAs attribute target ──────────────────────────────────────

test('CastableAs is an Attribute with TARGET_CLASS', function (): void {
    $ref = new ReflectionClass(CastableAs::class);
    $attrs = $ref->getAttributes(\Attribute::class);
    expect($attrs)->not->toBeEmpty('CastableAs must have #[Attribute]');

    $attr = $attrs[0]->newInstance();
    expect($attr->flags & Attribute::TARGET_CLASS)->toBeGreaterThan(0);
});

// ── Castable trait ─────────────────────────────────────────────────

test('Castable is a trait', function (): void {
    expect((new ReflectionClass(Castable::class))->isTrait())->toBeTrue();
});

test('Castable has castUsing method returning CastsAttributes', function (): void {
    $ref = new ReflectionClass(Castable::class);
    $m = $ref->getMethod('castUsing');
    expect($m->getReturnType()?->getName())->toBe('Illuminate\\Contracts\\Database\\Eloquent\\CastsAttributes');
});

// ── Console commands ────────────────────────────────────────────────

test('console commands have #[Override] on handle', function (): void {
    $commands = [
        \ZeroBoiler\ValueObjects\Console\Commands\MakeValueObjectCommand::class,
        \ZeroBoiler\ValueObjects\Console\Commands\ListValueObjectsCommand::class,
    ];

    foreach ($commands as $class) {
        $ref = new ReflectionClass($class);
        $m = $ref->getMethod('handle');
        expect($m->getAttributes(\Override::class))->not->toBeEmpty(
            "{$class}::handle() must have #[Override]"
        );
        expect($m->getReturnType()?->getName())->toBe('int');
    }
});

// ── ExchangeRateProvider API ─────────────────────────────────────────

test('ExchangeRateProvider has getRate method', function (): void {
    $ref = new ReflectionClass(ExchangeRateProvider::class);
    expect($ref->hasMethod('getRate'))->toBeTrue();
});

// ── Version consistency across phases ────────────────────────────────

test('Phase36 test version matches', function (): void {
    $content = file_get_contents(__DIR__ . '/Phase36ProductionReadinessTest.php');
    expect($content)->toContain("'1.42.0'");
});

test('Phase234 test version matches', function (): void {
    $content = file_get_contents(__DIR__ . '/Phase234ProductionTest.php');
    expect($content)->toContain("'1.42.0'");
});

// ── README version badge ────────────────────────────────────────────

test('README contains correct version badge', function (): void {
    $content = file_get_contents(__DIR__ . '/../README.md');
    expect($content)->toContain('version-1.43.0');
});

// ── phpstan level 9 ────────────────────────────────────────────────

test('phpstan.neon is configured at level 9', function (): void {
    $content = file_get_contents(__DIR__ . '/../phpstan.neon');
    expect($content)->toContain('->level(9)');
});

// ── rector PHP 8.5 target ──────────────────────────────────────────

test('rector.php targets PHP 8.5', function (): void {
    $content = file_get_contents(__DIR__ . '/../rector.php');
    expect($content)->toContain('UP_TO_PHP_85');
});

// ── Composer metadata integrity ───────────────────────────────────────

test('composer.json has correct metadata', function (): void {
    $json = json_decode(file_get_contents(__DIR__ . '/../composer.json'), true);

    expect($json['name'])->toBe('zeroboiler/value-objects');
    expect($json['type'])->toBe('library');
    expect($json['license'])->toBe('MIT');
    expect($json['require']['php'])->toBe('^8.5');
    expect($json['autoload']['psr-4']['ZeroBoiler\\\\ValueObjects\\\\'])->toBe('src/');
    expect($json['scripts']['test'])->toBe('pest');
    expect($json['scripts']['analyse'])->toBe('phpstan analyse');
    expect($json['scripts']['lint'])->toBe('pint');
    expect($json['scripts']['rector'])->toBe('rector');
});

test('composer.json extra.laravel provider is correct', function (): void {
    $json = json_decode(file_get_contents(__DIR__ . '/../composer.json'), true);

    expect($json['extra']['laravel']['providers'])->toContain(
        'ZeroBoiler\\\\ValueObjects\\\\ValueObjectsServiceProvider'
    );
});

// ── Cross-reference integrity ─────────────────────────────────────────

test('all expected directories exist in src/', function (): void {
    $srcDir = __DIR__ . '/../src';
    expect(is_dir($srcDir . '/Contracts'))->toBeTrue();
    expect(is_dir($srcDir . '/Exceptions'))->toBeTrue();
    expect(is_dir($srcDir . '/Console/Commands'))->toBeTrue();
});

// ── @since annotation completeness ──────────────────────────────────

test('all public classes have @since annotation', function (): void {
    $classes = [
        ValueObjectsException::class,
        InvalidValueObjectsArgumentException::class,
        ValueObjectsRuntimeException::class,
        ValueObject::class,
        ValueObjectContract::class,
        ValueObjectInterface::class,
        Address::class,
        Coordinates::class,
        Currency::class,
        Duration::class,
        Email::class,
        Money::class,
        Percentage::class,
        PhoneNumber::class,
        Url::class,
        Castable::class,
        CastableAs::class,
        ValueObjectCast::class,
        ExchangeRateProvider::class,
        ValueObjectsServiceProvider::class,
    ];

    foreach ($classes as $class) {
        $ref = new ReflectionClass($class);
        $doc = $ref->getDocComment() ?: '';
        expect($doc)
            ->toContain('@since', "{$class} must have @since annotation");
    }
});

// ── Test count tracking ────────────────────────────────────────────

test('assertion count tracking', function (): void {
    // Phase 37 adds ~50 new assertions
    expect(true)->toBeTrue();
})->skip('Counting test — not executed');
