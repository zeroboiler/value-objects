<?php

/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

use ZeroBoiler\ValueObjects\Address;
use ZeroBoiler\ValueObjects\Castable;
use ZeroBoiler\ValueObjects\CastableAs;
use ZeroBoiler\ValueObjects\Contracts\ValueObject as ValueObjectContract;
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

/**
 * Phase 52 production readiness — method-level @since annotation audit,
 * interface method count parity, VO API surface verification, redundant
 * import cleanup, constructor :void audit, readonly enforcement,
 * Castable/CastableAs/ValueObjectCast deep audit, and comprehensive
 * infrastructure verification.
 */
test('Phase 52: ValueObjectContract interface has 5 methods', function (): void {
    $ref = new ReflectionClass(ValueObjectContract::class);
    $methods = array_filter(
        $ref->getMethods(ReflectionMethod::IS_PUBLIC),
        fn (ReflectionMethod $m): bool => ! $m->isStatic(),
    );
    // toPrimitive, equals, columnType (own) + toArray, toJson, jsonSerialize (inherited from Arrayable/Jsonable/JsonSerializable/Stringable)
    // Public non-static: toPrimitive, equals, columnType, toArray (from Arrayable), toJson (from Jsonable), jsonSerialize (from JsonSerializable)
    // Static: fromPrimitive
    $allPublic = $ref->getMethods(ReflectionMethod::IS_PUBLIC);
    $ownMethods = array_filter($allPublic, fn (ReflectionMethod $m): bool => $m->getDeclaringClass()->getName() === ValueObjectContract::class);
    expect(count($ownMethods))->toBe(5); // toPrimitive, fromPrimitive, equals, columnType + static fromPrimitive
    expect($ref->hasMethod('toPrimitive'))->toBeTrue();
    expect($ref->hasMethod('fromPrimitive'))->toBeTrue();
    expect($ref->hasMethod('equals'))->toBeTrue();
    expect($ref->hasMethod('columnType'))->toBeTrue();
});

test('Phase 52: ExchangeRateProvider is interface with 1 method', function (): void {
    $ref = new ReflectionClass(ExchangeRateProvider::class);
    expect($ref->isInterface())->toBeTrue();
    $methods = $ref->getMethods(ReflectionMethod::IS_PUBLIC);
    expect(count($methods))->toBe(1);
    expect($ref->hasMethod('getRate'))->toBeTrue();
    expect($ref->getMethod('getRate')->getDocComment())->toContain('@since');
});

test('Phase 52: ValueObjectCast implements CastsAttributes with 3 public methods', function (): void {
    $ref = new ReflectionClass(ValueObjectCast::class);
    expect($ref->isFinal())->toBeTrue();
    expect($ref->implementsInterface(\Illuminate\Contracts\Database\Eloquent\CastsAttributes::class))->toBeTrue();
    $publicMethods = array_filter(
        $ref->getMethods(ReflectionMethod::IS_PUBLIC),
        fn (ReflectionMethod $m): bool => $m->getDeclaringClass()->getName() === ValueObjectCast::class,
    );
    expect(count($publicMethods))->toBe(3); // get, set, serialize
    foreach ($publicMethods as $method) {
        expect($method->getDocComment())->toContain('@since');
    }
});

test('Phase 52: Castable trait has 2 methods with @since', function (): void {
    $ref = new ReflectionClass(Castable::class);
    expect($ref->isTrait())->toBeTrue();
    $methods = $ref->getMethods(ReflectionMethod::IS_PUBLIC);
    expect(count($methods))->toBe(2); // castUsing, getCastAttributes
    foreach ($methods as $method) {
        expect($method->getDocComment())->toContain('@since');
    }
});

test('Phase 52: CastableAs attribute is final readonly CLASS target', function (): void {
    $ref = new ReflectionClass(CastableAs::class);
    expect($ref->isFinal())->toBeTrue();
    expect($ref->isReadOnly())->toBeTrue();
    $attrs = $ref->getAttributes(\Attribute::class);
    expect($attrs)->not->toBeEmpty();
    expect($attrs[0]->getArguments())->toContain(\Attribute::TARGET_CLASS);
});

test('Phase 52: all concrete VOs are final with readonly properties and constructor :void', function (): void {
    $vos = [Email::class, Url::class, PhoneNumber::class, Money::class, Address::class, Currency::class, Percentage::class, Duration::class, Coordinates::class];

    foreach ($vos as $fqcn) {
        $ref = new ReflectionClass($fqcn);
        expect($ref->isFinal())->toBeTrue("{$fqcn} must be final");
        expect($ref->isSubclassOf(ValueObject::class))->toBeTrue("{$fqcn} must extend ValueObject");

        // Constructor has :void
        $ctor = $ref->getConstructor();
        expect($ctor)->not->toBeNull("{$fqcn} must have a constructor");
        expect((string) $ctor->getReturnType())->toBe('void', "{$fqcn} constructor must return void");

        // At least one readonly property
        $readonlyProps = array_filter(
            $ref->getProperties(ReflectionProperty::IS_PUBLIC),
            fn (ReflectionProperty $p): bool => $p->isReadOnly(),
        );
        expect($readonlyProps)->not->toBeEmpty("{$fqcn} must have at least one readonly property");
    }
});

test('Phase 52: all concrete VOs use Castable trait', function (): void {
    $vos = [Email::class, Url::class, PhoneNumber::class, Money::class, Address::class, Currency::class, Percentage::class, Duration::class, Coordinates::class];

    foreach ($vos as $fqcn) {
        expect(in_array(Castable::class, class_uses($fqcn), true))->toBeTrue("{$fqcn} must use Castable trait");
    }
});

test('Phase 52: all concrete VOs override toPrimitive/fromPrimitive/columnType', function (): void {
    $vos = [Email::class, Url::class, PhoneNumber::class, Money::class, Address::class, Currency::class, Percentage::class, Duration::class, Coordinates::class];
    $baseRef = new ReflectionClass(ValueObject::class);

    foreach ($vos as $fqcn) {
        $ref = new ReflectionClass($fqcn);
        expect($ref->getMethod('toPrimitive')->getDeclaringClass()->getName())->toBe($fqcn, "{$fqcn} must override toPrimitive");
        expect($ref->getMethod('fromPrimitive')->getDeclaringClass()->getName())->toBe($fqcn, "{$fqcn} must override fromPrimitive");
        expect($ref->getMethod('columnType')->getDeclaringClass()->getName())->toBe($fqcn, "{$fqcn} must override columnType");
    }
});

test('Phase 52: ValueObjectInterface is deprecated and extends ValueObjectContract', function (): void {
    $ref = new ReflectionClass(ValueObjectInterface::class);
    expect($ref->isInterface())->toBeTrue();
    expect($ref->getDocComment())->toContain('@deprecated');
    $interfaces = $ref->getInterfaceNames();
    expect($interfaces)->toContain(ValueObjectContract::class);
});

test('Phase 52: ValueObjectInterface has no redundant imports', function (): void {
    $content = file_get_contents((new ReflectionClass(ValueObjectInterface::class))->getFileName());
    // Should import ValueObjectContract with alias, but NOT also import ValueObject without alias
    $lines = explode("\n", $content);
    $useStatements = array_filter($lines, fn (string $l): bool => str_starts_with(trim($l), 'use '));
    $nonAliased = array_filter($useStatements, fn (string $l): bool =>
        str_contains($l, 'Contracts\\ValueObject') && ! str_contains($l, ' as ')
    );
    expect(count($nonAliased))->toBe(0, 'ValueObjectInterface should not have a redundant non-aliased import of Contracts\\ValueObject');
});

test('Phase 52: ValueObject equals() uses null check not instanceof', function (): void {
    $content = file_get_contents((new ReflectionClass(ValueObject::class))->getFileName());
    expect($content)->toContain('if ($other === null)');
    expect($content)->not->toContain('if (! $other instanceof ValueObjectContract)');
});

test('Phase 52: Money API surface — 30+ public methods', function (): void {
    $ref = new ReflectionClass(Money::class);
    $publicMethods = array_filter(
        $ref->getMethods(ReflectionMethod::IS_PUBLIC),
        fn (ReflectionMethod $m): bool => $m->getDeclaringClass()->getName() === Money::class,
    );
    expect(count($publicMethods))->toBeGreaterThanOrEqual(30);
    // Key methods
    expect($ref->hasMethod('add'))->toBeTrue();
    expect($ref->hasMethod('subtract'))->toBeTrue();
    expect($ref->hasMethod('multiply'))->toBeTrue();
    expect($ref->hasMethod('divide'))->toBeTrue();
    expect($ref->hasMethod('convert'))->toBeTrue();
    expect($ref->hasMethod('convertTo'))->toBeTrue();
    expect($ref->hasMethod('convertVia'))->toBeTrue();
    expect($ref->hasMethod('allocate'))->toBeTrue();
    expect($ref->hasMethod('allocateRatios'))->toBeTrue();
    expect($ref->hasMethod('percentage'))->toBeTrue();
    expect($ref->hasMethod('format'))->toBeTrue();
    expect($ref->hasMethod('toMajor'))->toBeTrue();
    expect($ref->hasMethod('fromMajor'))->toBeTrue();
    expect($ref->hasMethod('isZero'))->toBeTrue();
    expect($ref->hasMethod('isPositive'))->toBeTrue();
    expect($ref->hasMethod('isNegative'))->toBeTrue();
    expect($ref->hasMethod('greaterThan'))->toBeTrue();
    expect($ref->hasMethod('lessThan'))->toBeTrue();
    expect($ref->hasMethod('greaterThanOrEqual'))->toBeTrue();
    expect($ref->hasMethod('lessThanOrEqual'))->toBeTrue();
    // Static factories
    expect($ref->hasMethod('usd'))->toBeTrue();
    expect($ref->hasMethod('eur'))->toBeTrue();
    expect($ref->hasMethod('gbp'))->toBeTrue();
    expect($ref->hasMethod('jpy'))->toBeTrue();
});

test('Phase 52: Url API surface — 15+ public methods', function (): void {
    $ref = new ReflectionClass(Url::class);
    $publicMethods = array_filter(
        $ref->getMethods(ReflectionMethod::IS_PUBLIC),
        fn (ReflectionMethod $m): bool => $m->getDeclaringClass()->getName() === Url::class,
    );
    expect(count($publicMethods))->toBeGreaterThanOrEqual(15);
    // Key methods
    expect($ref->hasMethod('scheme'))->toBeTrue();
    expect($ref->hasMethod('host'))->toBeTrue();
    expect($ref->hasMethod('path'))->toBeTrue();
    expect($ref->hasMethod('query'))->toBeTrue();
    expect($ref->hasMethod('fragment'))->toBeTrue();
    expect($ref->hasMethod('queryParams'))->toBeTrue();
    expect($ref->hasMethod('isHttps'))->toBeTrue();
    expect($ref->hasMethod('isHttp'))->toBeTrue();
    expect($ref->hasMethod('withScheme'))->toBeTrue();
    expect($ref->hasMethod('toExpandedArray'))->toBeTrue();
});

test('Phase 52: PhoneNumber API surface — countryCode, format, toPrimitive', function (): void {
    $ref = new ReflectionClass(PhoneNumber::class);
    expect($ref->hasMethod('countryCode'))->toBeTrue();
    expect($ref->hasMethod('format'))->toBeTrue();
    expect($ref->hasMethod('toPrimitive'))->toBeTrue();
    expect($ref->hasMethod('fromPrimitive'))->toBeTrue();
    expect($ref->hasMethod('columnType'))->toBeTrue();
});

test('Phase 52: Currency API surface — decimalPlaces, subunitDivisor, subunitName, symbol', function (): void {
    $ref = new ReflectionClass(Currency::class);
    expect($ref->hasMethod('decimalPlaces'))->toBeTrue();
    expect($ref->hasMethod('subunitDivisor'))->toBeTrue();
    expect($ref->hasMethod('subunitName'))->toBeTrue();
    expect($ref->hasMethod('symbol'))->toBeTrue();
    expect($ref->hasMethod('fromCode'))->toBeTrue();
    expect($ref->hasMethod('validCodes'))->toBeTrue();
    expect($ref->hasMethod('isValid'))->toBeTrue();
});

test('Phase 52: Duration API surface — fromSeconds/Minutes/Hours/Days/Human, toSeconds/Minutes/Hours/Days', function (): void {
    $ref = new ReflectionClass(Duration::class);
    expect($ref->hasMethod('fromSeconds'))->toBeTrue();
    expect($ref->hasMethod('fromMinutes'))->toBeTrue();
    expect($ref->hasMethod('fromHours'))->toBeTrue();
    expect($ref->hasMethod('fromDays'))->toBeTrue();
    expect($ref->hasMethod('fromHuman'))->toBeTrue();
    expect($ref->hasMethod('toSeconds'))->toBeTrue();
    expect($ref->hasMethod('toMinutes'))->toBeTrue();
    expect($ref->hasMethod('toHours'))->toBeTrue();
    expect($ref->hasMethod('toDays'))->toBeTrue();
    expect($ref->hasMethod('humanReadable'))->toBeTrue();
    expect($ref->hasMethod('clampToZero'))->toBeTrue();
});

test('Phase 52: Coordinates API surface — distanceTo, distanceToKm, distanceToMiles', function (): void {
    $ref = new ReflectionClass(Coordinates::class);
    expect($ref->hasMethod('distanceTo'))->toBeTrue();
    expect($ref->hasMethod('distanceToKm'))->toBeTrue();
    expect($ref->hasMethod('distanceToMiles'))->toBeTrue();
});

test('Phase 52: Address API surface — full, lines, 6 readonly properties', function (): void {
    $ref = new ReflectionClass(Address::class);
    expect($ref->hasMethod('full'))->toBeTrue();
    expect($ref->hasMethod('lines'))->toBeTrue();
    $readonlyProps = array_filter(
        $ref->getProperties(ReflectionProperty::IS_PUBLIC),
        fn (ReflectionProperty $p): bool => $p->isReadOnly(),
    );
    expect(count($readonlyProps))->toBe(6); // street, street2, city, state, postalCode, country
});

test('Phase 52: Percentage API surface — of, applyTo, add, subtract, multiply, isZero, isFull', function (): void {
    $ref = new ReflectionClass(Percentage::class);
    expect($ref->hasMethod('of'))->toBeTrue();
    expect($ref->hasMethod('applyTo'))->toBeTrue();
    expect($ref->hasMethod('add'))->toBeTrue();
    expect($ref->hasMethod('subtract'))->toBeTrue();
    expect($ref->hasMethod('multiply'))->toBeTrue();
    expect($ref->hasMethod('isZero'))->toBeTrue();
    expect($ref->hasMethod('isFull'))->toBeTrue();
});

test('Phase 52: Email API surface — domain, localPart, value', function (): void {
    $ref = new ReflectionClass(Email::class);
    expect($ref->hasMethod('domain'))->toBeTrue();
    expect($ref->hasMethod('localPart'))->toBeTrue();
    expect($ref->getProperty('value')->isReadOnly())->toBeTrue();
});

test('Phase 52: exception leaf factories return self', function (): void {
    $leaves = [
        InvalidValueObjectsArgumentException::class,
        ValueObjectsRuntimeException::class,
    ];

    foreach ($leaves as $fqcn) {
        $method = new ReflectionMethod($fqcn, 'forMessage');
        expect((string) $method->getReturnType())->toBe($fqcn, "{$fqcn}::forMessage must return self");
    }
});

test('Phase 52: exception leaf constructors have non-empty default messages', function (): void {
    $leaves = [
        InvalidValueObjectsArgumentException::class => 'Invalid value-objects argument.',
        ValueObjectsRuntimeException::class => 'Value-objects runtime error.',
    ];

    foreach ($leaves as $fqcn => $expectedDefault) {
        $instance = $fqcn::forMessage('');
        // The default message should be used when empty string is passed
        // But forMessage passes empty string, so constructor applies default
        expect($instance->getMessage())->toBe($expectedDefault);
    }
});

test('Phase 52: ServiceProvider final + provides empty + #[Override]', function (): void {
    $ref = new ReflectionClass(ValueObjectsServiceProvider::class);
    expect($ref->isFinal())->toBeTrue();
    expect($ref->isSubclassOf(\Illuminate\Support\ServiceProvider::class))->toBeTrue();

    $provides = $ref->getMethod('provides');
    expect($provides->getDeclaringClass()->getName())->toBe(ValueObjectsServiceProvider::class);
    expect($provides->hasReturnType())->toBeTrue();
    expect((string) $provides->getReturnType())->toBe('array');
    $attrs = $provides->getAttributes(\Override::class);
    expect(count($attrs))->toBe(1);

    $register = $ref->getMethod('register');
    expect($register->hasReturnType())->toBeTrue();
    expect((string) $register->getReturnType())->toBe('void');
    $attrs = $register->getAttributes(\Override::class);
    expect(count($attrs))->toBe(1);

    $boot = $ref->getMethod('boot');
    expect($boot->hasReturnType())->toBeTrue();
    expect((string) $boot->getReturnType())->toBe('void');
    $attrs = $boot->getAttributes(\Override::class);
    expect(count($attrs))->toBe(1);
});

test('Phase 52: console commands final + #[Override] handle + int return', function (): void {
    $commands = [
        \ZeroBoiler\ValueObjects\Console\Commands\MakeValueObjectCommand::class,
        \ZeroBoiler\ValueObjects\Console\Commands\ListValueObjectsCommand::class,
    ];

    foreach ($commands as $fqcn) {
        $ref = new ReflectionClass($fqcn);
        expect($ref->isFinal())->toBeTrue("{$fqcn} must be final");

        $handle = $ref->getMethod('handle');
        expect($handle->hasReturnType())->toBeTrue();
        expect((string) $handle->getReturnType())->toBe('int', "{$fqcn}::handle must return int");
        $attrs = $handle->getAttributes(\Override::class);
        expect(count($attrs))->toBe(1, "{$fqcn}::handle must have #[Override]");
    }
});

test('Phase 52: source file count is 22', function (): void {
    $srcDir = __DIR__.'/../src';
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($srcDir, FilesystemIterator::SKIP_DOTS),
    );
    $count = 0;
    foreach ($files as $file) {
        if ($file->getExtension() === 'php') {
            $count++;
        }
    }
    expect($count)->toBe(22);
});

test('Phase 52: test file count and fixture count', function (): void {
    $testDir = __DIR__;
    $allFiles = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($testDir, FilesystemIterator::SKIP_DOTS),
    );
    $testCount = 0;
    $fixtureCount = 0;
    foreach ($allFiles as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }
        $name = $file->getFilename();
        if ($name === 'Pest.php' || $name === 'bootstrap.php' || $name === 'helpers.php') {
            continue;
        }
        if (str_contains($file->getPath(), 'Fixtures')) {
            $fixtureCount++;
        } else {
            $testCount++;
        }
    }
    expect($testCount)->toBeGreaterThanOrEqual(38);
    expect($fixtureCount)->toBeGreaterThanOrEqual(1);
});

test('Phase 52: phpstan.neon ↔ neon.dist parity', function (): void {
    $neon = file_get_contents(__DIR__.'/../phpstan.neon');
    $dist = file_get_contents(__DIR__.'/../phpstan.neon.dist');

    // Both should have the same level
    expect($neon)->toContain('level: 9');
    expect($dist)->toContain('level: 9');

    // Check key parameters match
    expect($neon)->toContain('treatPhpDocTypesAsCertain: false');
    expect($dist)->toContain('treatPhpDocTypesAsCertain: false');
    expect($neon)->toContain('reportUnmatchedIgnoredErrors: false');
    expect($dist)->toContain('reportUnmatchedIgnoredErrors: false');
    expect($neon)->toContain('checkGenericClassInNonGenericObjectType: true');
    expect($dist)->toContain('checkGenericClassInNonGenericObjectType: true');
    expect($neon)->toContain('checkMissingIterableValueType: false');
    expect($dist)->toContain('checkMissingIterableValueType: false');
});

test('Phase 52: rector PHP 8.5 target', function (): void {
    $content = file_get_contents(__DIR__.'/../rector.php');
    expect($content)->toContain('php85');
});

test('Phase 52: composer metadata integrity', function (): void {
    $composer = json_decode(file_get_contents(__DIR__.'/../composer.json'), true);

    expect($composer['name'])->toBe('zeroboiler/value-objects');
    expect($composer['require']['php'])->toBe('^8.5');
    expect($composer['require']['illuminate/contracts'])->toBe('^13.0');
    expect($composer['autoload']['psr-4']['ZeroBoiler\\\\ValueObjects\\\\'])->toBe('src/');
    expect($composer['autoload-dev']['psr-4']['ZeroBoiler\\\\ValueObjects\\\\Tests\\\\'])->toBe('tests/');
    expect($composer['extra']['laravel']['providers'])->toContain('ZeroBoiler\\\\ValueObjects\\\\ValueObjectsServiceProvider');
    expect($composer['suggest'])->toHaveKey('giggsey/libphonenumber-for-php');
});

test('Phase 52: all public methods on ValueObject base have return types', function (): void {
    $ref = new ReflectionClass(ValueObject::class);
    $methods = $ref->getMethods(ReflectionMethod::IS_PUBLIC);
    foreach ($methods as $method) {
        if ($method->getDeclaringClass()->getName() !== ValueObject::class) {
            continue;
        }
        expect($method->hasReturnType())->toBeTrue("ValueObject::{$method->getName()} must have a return type");
    }
});

test('Phase 52: ValueObjectCast constructor is :void', function (): void {
    $ctor = (new ReflectionClass(ValueObjectCast::class))->getConstructor();
    expect($ctor)->not->toBeNull();
    expect((string) $ctor->getReturnType())->toBe('void');
});

test('Phase 52: all VO classes have #[Override] on toArray, __toString, toPrimitive, fromPrimitive, columnType', function (): void {
    $vos = [Email::class, Url::class, PhoneNumber::class, Money::class, Address::class, Currency::class, Percentage::class, Duration::class, Coordinates::class];

    foreach ($vos as $fqcn) {
        $ref = new ReflectionClass($fqcn);

        $methodsToCheck = ['toArray', '__toString', 'toPrimitive', 'fromPrimitive', 'columnType'];
        foreach ($methodsToCheck as $methodName) {
            $method = $ref->getMethod($methodName);
            $attrs = $method->getAttributes(\Override::class);
            expect(count($attrs))->toBe(1, "{$fqcn}::{$methodName} must have #[Override]");
        }
    }
});

test('Phase 52: Money static factory methods have @since annotations', function (): void {
    $ref = new ReflectionClass(Money::class);
    $factories = ['usd', 'eur', 'gbp', 'jpy', 'fromMajor'];
    foreach ($factories as $method) {
        $doc = $ref->getMethod($method)->getDocComment();
        expect($doc)->toContain('@since', "Money::{$method} must have @since annotation");
    }
});
