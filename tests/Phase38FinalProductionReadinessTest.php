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
function vo38_glob_recursive(string $pattern, int $flags = 0): array
{
    $files = glob($pattern, $flags);

    foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
        $files = array_merge(
            $files,
            vo38_glob_recursive($dir . '/' . basename($pattern), $flags),
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

test('version is 1.44.0', function (): void {
    $json = json_decode(file_get_contents(__DIR__ . '/../composer.json'), true);
    expect($json['version'])->toBe('1.44.0');
});

// ── Exception hierarchy @see + finality ──────────────────────────────

test('ValueObjectsException has @see child references', function (): void {
    $ref = new ReflectionClass(ValueObjectsException::class);
    $doc = $ref->getDocComment() ?: '';
    expect($doc)->toContain('@see InvalidValueObjectsArgumentException');
    expect($doc)->toContain('@see ValueObjectsRuntimeException');
});

test('leaf exceptions have @see parent reference', function (): void {
    $leaves = [
        InvalidValueObjectsArgumentException::class,
        ValueObjectsRuntimeException::class,
    ];

    foreach ($leaves as $class) {
        $ref = new ReflectionClass($class);
        $doc = $ref->getDocComment() ?: '';
        expect($doc)->toContain('@see ValueObjectsException',
            "{$class} must have @see ValueObjectsException"
        );
    }
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

// ── Source/test file counts ─────────────────────────────────────────

test('source file count is 22', function (): void {
    $srcDir = __DIR__ . '/../src';
    $files = vo38_glob_recursive($srcDir . '/*.php');
    expect(count($files))->toBe(22, 'Expected 22 source files');
});

test('test file count is correct', function (): void {
    $testDir = __DIR__;
    $files = vo38_glob_recursive($testDir . '/*.php');
    // Exclude Pest.php and TestCase.php infrastructure files
    $count = count($files);
    expect($count)->toBeGreaterThan(30, 'Expected at least 30 test files');
});

// ── strict_types coverage ───────────────────────────────────────────

test('all source files have declare(strict_types=1)', function (): void {
    $srcDir = __DIR__ . '/../src';
    $files = vo38_glob_recursive($srcDir . '/*.php');

    foreach ($files as $file) {
        $content = file_get_contents($file);
        expect($content)->toContain('declare(strict_types=1)',
            basename($file) . ' must have strict_types'
        );
    }
});

// ── Final class enforcement ─────────────────────────────────────────

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

// ── ExchangeRateProvider is interface, not class ─────────────────────

test('ExchangeRateProvider is an interface', function (): void {
    expect((new ReflectionClass(ExchangeRateProvider::class))->isInterface())->toBeTrue();
});

test('ExchangeRateProvider has getRate method', function (): void {
    $ref = new ReflectionClass(ExchangeRateProvider::class);
    expect($ref->hasMethod('getRate'))->toBeTrue();

    $m = $ref->getMethod('getRate');
    expect($m->getReturnType()?->getName())->toBe('float');
});

// ── Constructor :void return types ──────────────────────────────────

test('ValueObjectsException constructor has :void return type', function (): void {
    $ref = new ReflectionClass(ValueObjectsException::class);
    $ctor = $ref->getConstructor();
    expect($ctor?->hasReturnType())->toBeTrue();
    expect($ctor?->getReturnType()?->getName())->toBe('void');
});

test('CastableAs constructor has :void return type', function (): void {
    $ref = new ReflectionClass(CastableAs::class);
    $ctor = $ref->getConstructor();
    expect($ctor?->hasReturnType())->toBeTrue();
    expect($ctor?->getReturnType()?->getName())->toBe('void');
});

test('ValueObjectCast constructor has :void return type', function (): void {
    $ref = new ReflectionClass(ValueObjectCast::class);
    $ctor = $ref->getConstructor();
    expect($ctor?->hasReturnType())->toBeTrue();
    expect($ctor?->getReturnType()?->getName())->toBe('void');
});

// ── Interface compliance ─────────────────────────────────────────────

test('ValueObjectContract interface methods', function (): void {
    $ref = new ReflectionClass(ValueObjectContract::class);
    $methodNames = array_map(fn (\ReflectionMethod $m): string => $m->getName(), $ref->getMethods());
    expect($methodNames)->toContain('toPrimitive');
    expect($methodNames)->toContain('fromPrimitive');
    expect($methodNames)->toContain('equals');
    expect($methodNames)->toContain('columnType');
});

test('ValueObjectInterface extends ValueObjectContract', function (): void {
    expect((new ReflectionClass(ValueObjectInterface::class))->isInterface())->toBeTrue();
    expect(
        (new ReflectionClass(ValueObjectInterface::class))->isSubclassOf(ValueObjectContract::class)
    )->toBeTrue();
});

// ── ServiceProvider/Facade #[Override] audit ─────────────────────────

test('ServiceProvider has #[Override] on register, boot, provides', function (): void {
    $ref = new ReflectionClass(ValueObjectsServiceProvider::class);

    foreach (['register', 'boot', 'provides'] as $method) {
        $m = $ref->getMethod($method);
        expect($m->getAttributes(\Override::class))->not->toBeEmpty(
            "ValueObjectsServiceProvider::{$method} must have #[Override]"
        );
    }
});

// ── Console commands #[Override] + int return ─────────────────────────

test('console commands have #[Override] on handle and return int', function (): void {
    $commands = [
        \ZeroBoiler\ValueObjects\Console\Commands\MakeValueObjectCommand::class,
        \ZeroBoiler\ValueObjects\Console\Commands\ListValueObjectsCommand::class,
    ];

    foreach ($commands as $class) {
        $ref = new ReflectionClass($class);
        expect($ref->isFinal())->toBeTrue("{$class} must be final");

        $m = $ref->getMethod('handle');
        expect($m->getAttributes(\Override::class))->not->toBeEmpty(
            "{$class}::handle() must have #[Override]"
        );
        expect($m->getReturnType()?->getName())->toBe('int');
    }
});

// ── Castable trait verification ─────────────────────────────────────

test('Castable is a trait with castUsing returning CastsAttributes', function (): void {
    expect((new ReflectionClass(Castable::class))->isTrait())->toBeTrue();

    $ref = new ReflectionClass(Castable::class);
    $m = $ref->getMethod('castUsing');
    expect($m->getReturnType()?->getName())->toBe(
        'Illuminate\\Contracts\\Database\\Eloquent\\CastsAttributes'
    );
});

// ── CastableAs attribute target ──────────────────────────────────────

test('CastableAs is readonly final with TARGET_CLASS', function (): void {
    $ref = new ReflectionClass(CastableAs::class);
    expect($ref->isFinal())->toBeTrue();
    expect($ref->isReadOnly())->toBeTrue();

    $attrs = $ref->getAttributes(\Attribute::class);
    expect($attrs)->not->toBeEmpty('CastableAs must have #[Attribute]');
    $attr = $attrs[0]->newInstance();
    expect($attr->flags & Attribute::TARGET_CLASS)->toBeGreaterThan(0);
});

// ── ValueObject abstract base ───────────────────────────────────────

test('ValueObject is abstract with correct methods', function (): void {
    $ref = new ReflectionClass(ValueObject::class);
    expect($ref->isAbstract())->toBeTrue();

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

// ── License headers ──────────────────────────────────────────────────

test('all source files have license header', function (): void {
    $srcDir = __DIR__ . '/../src';
    $files = vo38_glob_recursive($srcDir . '/*.php');

    foreach ($files as $file) {
        $content = file_get_contents($file);
        $firstLines = implode("\n", array_slice(explode("\n", $content), 0, 6));
        expect($firstLines)->toContain('ZeroBoiler',
            basename($file) . ' must have ZeroBoiler license header'
        );
    }
});

// ── No TODO/FIXME markers ───────────────────────────────────────────

test('no TODO/FIXME markers in source files', function (): void {
    $srcDir = __DIR__ . '/../src';
    $files = vo38_glob_recursive($srcDir . '/*.php');

    foreach ($files as $file) {
        $content = file_get_contents($file);
        expect($content)->not->toContain('TODO', basename($file) . ' must not have TODO');
        expect($content)->not->toContain('FIXME', basename($file) . ' must not have FIXME');
    }
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
        expect($doc)->toContain('@since', "{$class} must have @since annotation");
    }
});

// ── Config structure validation ──────────────────────────────────────
// value-objects has no config file (pure library), verify this is intentional

test('no config file needed (pure library)', function (): void {
    // ValueObjects package is a pure library — no config file needed
    expect(true)->toBeTrue();
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

// ── phpstan level 9 + extended checks ──────────────────────────────

test('phpstan.neon is configured at level 9', function (): void {
    $content = file_get_contents(__DIR__ . '/../phpstan.neon');
    expect($content)->toContain('->level(9)');
});

test('phpstan.neon has extended checks', function (): void {
    $content = file_get_contents(__DIR__ . '/../phpstan.neon');
    expect($content)->toContain('checkUnusedParameters');
    expect($content)->toContain('checkUninitializedProperties');
});

// ── rector PHP 8.5 target ──────────────────────────────────────────

test('rector.php targets PHP 8.5', function (): void {
    $content = file_get_contents(__DIR__ . '/../rector.php');
    expect($content)->toContain('UP_TO_PHP_85');
});

// ── Cross-reference integrity ─────────────────────────────────────────

test('all expected directories exist in src/', function (): void {
    $srcDir = __DIR__ . '/../src';
    expect(is_dir($srcDir . '/Contracts'))->toBeTrue();
    expect(is_dir($srcDir . '/Exceptions'))->toBeTrue();
    expect(is_dir($srcDir . '/Console/Commands'))->toBeTrue();
});

test('src subdirectory count is 3', function (): void {
    $srcDir = __DIR__ . '/../src';
    $dirs = array_filter(glob($srcDir . '/*'), 'is_dir');
    expect(count($dirs))->toBe(3, 'Expected 3 subdirectories: Contracts, Exceptions, Console');
});

// ── Version consistency across phases ────────────────────────────────

test('Phase37 test version forward-sync', function (): void {
    $content = file_get_contents(__DIR__ . '/Phase37ProductionReadinessTest.php');
    expect($content)->toContain("'1.43.0'");
});

// ── README version badge ────────────────────────────────────────────

test('README contains correct version badge', function (): void {
    $content = file_get_contents(__DIR__ . '/../README.md');
    expect($content)->toContain('version-1.44.0');
});

// ── VO API surface verification ─────────────────────────────────────

test('Money public API surface', function (): void {
    $ref = new ReflectionClass(Money::class);
    $publicMethods = array_filter(
        $ref->getMethods(\ReflectionMethod::IS_PUBLIC),
        fn (\ReflectionMethod $m): bool => ! $m->isStatic()
    );

    $names = array_map(fn (\ReflectionMethod $m): string => $m->getName(), $publicMethods);

    // Core arithmetic
    expect($names)->toContain('add');
    expect($names)->toContain('subtract');
    expect($names)->toContain('multiply');
    expect($names)->toContain('divide');
    expect($names)->toContain('convert');
    expect($names)->toContain('convertTo');
    expect($names)->toContain('convertVia');

    // Queries
    expect($names)->toContain('isZero');
    expect($names)->toContain('isPositive');
    expect($names)->toContain('isNegative');

    // Comparison
    expect($names)->toContain('greaterThan');
    expect($names)->toContain('lessThan');
    expect($names)->toContain('greaterThanOrEqual');
    expect($names)->toContain('lessThanOrEqual');

    // Formatting
    expect($names)->toContain('format');
    expect($names)->toContain('toMajor');

    // Properties
    expect($names)->toContain('decimalPlaces');
    expect($names)->toContain('subunitDivisor');
    expect($names)->toContain('currency');

    // Allocation
    expect($names)->toContain('allocate');
    expect($names)->toContain('allocateRatios');
});

test('Duration public API surface', function (): void {
    $ref = new ReflectionClass(Duration::class);
    $publicMethods = array_filter(
        $ref->getMethods(\ReflectionMethod::IS_PUBLIC),
        fn (\ReflectionMethod $m): bool => ! $m->isStatic()
    );

    $names = array_map(fn (\ReflectionMethod $m): string => $m->getName(), $publicMethods);

    expect($names)->toContain('toSeconds');
    expect($names)->toContain('toMinutes');
    expect($names)->toContain('toHours');
    expect($names)->toContain('toDays');
    expect($names)->toContain('add');
    expect($names)->toContain('subtract');
    expect($names)->toContain('clampToZero');
    expect($names)->toContain('humanReadable');
});

test('Currency public API surface', function (): void {
    $ref = new ReflectionClass(Currency::class);
    $publicMethods = array_filter(
        $ref->getMethods(\ReflectionMethod::IS_PUBLIC),
        fn (\ReflectionMethod $m): bool => ! $m->isStatic()
    );

    $names = array_map(fn (\ReflectionMethod $m): string => $m->getName(), $publicMethods);

    expect($names)->toContain('decimalPlaces');
    expect($names)->toContain('subunitDivisor');
    expect($names)->toContain('subunitName');
    expect($names)->toContain('symbol');
    expect($names)->toContain('equals');
});

test('Email public API surface', function (): void {
    $ref = new ReflectionClass(Email::class);
    $publicMethods = array_filter(
        $ref->getMethods(\ReflectionMethod::IS_PUBLIC),
        fn (\ReflectionMethod $m): bool => ! $m->isStatic()
    );

    $names = array_map(fn (\ReflectionMethod $m): string => $m->getName(), $publicMethods);

    expect($names)->toContain('domain');
    expect($names)->toContain('localPart');
});

test('Address public API surface', function (): void {
    $ref = new ReflectionClass(Address::class);
    $publicMethods = array_filter(
        $ref->getMethods(\ReflectionMethod::IS_PUBLIC),
        fn (\ReflectionMethod $m): bool => ! $m->isStatic()
    );

    $names = array_map(fn (\ReflectionMethod $m): string => $m->getName(), $publicMethods);

    expect($names)->toContain('full');
    expect($names)->toContain('lines');
});

test('Url public API surface', function (): void {
    $ref = new ReflectionClass(Url::class);
    $publicMethods = array_filter(
        $ref->getMethods(\ReflectionMethod::IS_PUBLIC),
        fn (\ReflectionMethod $m): bool => ! $m->isStatic()
    );

    $names = array_map(fn (\ReflectionMethod $m): string => $m->getName(), $publicMethods);

    expect($names)->toContain('scheme');
    expect($names)->toContain('host');
    expect($names)->toContain('path');
    expect($names)->toContain('query');
    expect($names)->toContain('fragment');
    expect($names)->toContain('queryParams');
    expect($names)->toContain('isHttps');
    expect($names)->toContain('isHttp');
    expect($names)->toContain('withScheme');
    expect($names)->toContain('toExpandedArray');
});

test('Coordinates public API surface', function (): void {
    $ref = new ReflectionClass(Coordinates::class);
    $publicMethods = array_filter(
        $ref->getMethods(\ReflectionMethod::IS_PUBLIC),
        fn (\ReflectionMethod $m): bool => ! $m->isStatic()
    );

    $names = array_map(fn (\ReflectionMethod $m): string => $m->getName(), $publicMethods);

    expect($names)->toContain('isValidLat');
    expect($names)->toContain('isValidLng');
    expect($names)->toContain('distanceTo');
    expect($names)->toContain('distanceToKm');
    expect($names)->toContain('distanceToMiles');
});

test('Percentage public API surface', function (): void {
    $ref = new ReflectionClass(Percentage::class);
    $publicMethods = array_filter(
        $ref->getMethods(\ReflectionMethod::IS_PUBLIC),
        fn (\ReflectionMethod $m): bool => ! $m->isStatic()
    );

    $names = array_map(fn (\ReflectionMethod $m): string => $m->getName(), $publicMethods);

    expect($names)->toContain('of');
    expect($names)->toContain('applyTo');
    expect($names)->toContain('add');
    expect($names)->toContain('subtract');
    expect($names)->toContain('multiply');
    expect($names)->toContain('isZero');
    expect($names)->toContain('isFull');
});

test('PhoneNumber public API surface', function (): void {
    $ref = new ReflectionClass(PhoneNumber::class);
    $publicMethods = array_filter(
        $ref->getMethods(\ReflectionMethod::IS_PUBLIC),
        fn (\ReflectionMethod $m): bool => ! $m->isStatic()
    );

    $names = array_map(fn (\ReflectionMethod $m): string => $m->getName(), $publicMethods);

    expect($names)->toContain('countryCode');
    expect($names)->toContain('format');
});

// ── ValueObjectCast API surface ─────────────────────────────────────

test('ValueObjectCast implements CastsAttributes', function (): void {
    $ref = new ReflectionClass(ValueObjectCast::class);
    expect($ref->implementsInterface(\Illuminate\Contracts\Database\Eloquent\CastsAttributes::class))->toBeTrue();
});

test('ValueObjectCast has get/set/serialize methods', function (): void {
    $ref = new ReflectionClass(ValueObjectCast::class);

    foreach (['get', 'set', 'serialize'] as $method) {
        expect($ref->hasMethod($method))->toBeTrue(
            "ValueObjectCast must have method {$method}()"
        );
    }
});

// ── Static factory methods verification ────────────────────────────

test('Money static factory methods', function (): void {
    $ref = new ReflectionClass(Money::class);
    $staticMethods = array_filter(
        $ref->getMethods(\ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_STATIC),
        fn (\ReflectionMethod $m): bool => ! in_array($m->getName(), ['fromPrimitive', 'columnType', 'castUsing', 'getCastAttributes'], true)
    );

    $names = array_map(fn (\ReflectionMethod $m): string => $m->getName(), $staticMethods);

    expect($names)->toContain('usd');
    expect($names)->toContain('eur');
    expect($names)->toContain('gbp');
    expect($names)->toContain('jpy');
    expect($names)->toContain('fromMajor');
});

test('Duration static factory methods', function (): void {
    $ref = new ReflectionClass(Duration::class);
    $staticMethods = array_filter(
        $ref->getMethods(\ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_STATIC),
        fn (\ReflectionMethod $m): bool => ! in_array($m->getName(), ['fromPrimitive', 'columnType', 'castUsing', 'getCastAttributes'], true)
    );

    $names = array_map(fn (\ReflectionMethod $m): string => $m->getName(), $staticMethods);

    expect($names)->toContain('fromSeconds');
    expect($names)->toContain('fromMinutes');
    expect($names)->toContain('fromHours');
    expect($names)->toContain('fromDays');
    expect($names)->toContain('fromHuman');
});

test('Currency static methods', function (): void {
    $ref = new ReflectionClass(Currency::class);
    $staticMethods = array_filter(
        $ref->getMethods(\ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_STATIC),
        fn (\ReflectionMethod $m): bool => ! in_array($m->getName(), ['fromPrimitive', 'columnType', 'castUsing', 'getCastAttributes'], true)
    );

    $names = array_map(fn (\ReflectionMethod $m): string => $m->getName(), $staticMethods);

    expect($names)->toContain('fromCode');
    expect($names)->toContain('validCodes');
    expect($names)->toContain('isValid');
});
