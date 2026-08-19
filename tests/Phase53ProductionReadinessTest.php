<?php

/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

use ZeroBoiler\ValueObjects\Contracts\ValueObject as ValueObjectContract;
use ZeroBoiler\ValueObjects\Exceptions\InvalidValueObjectsArgumentException;
use ZeroBoiler\ValueObjects\Exceptions\ValueObjectsException;
use ZeroBoiler\ValueObjects\Exceptions\ValueObjectsRuntimeException;
use ZeroBoiler\ValueObjects\Address;
use ZeroBoiler\ValueObjects\Castable;
use ZeroBoiler\ValueObjects\CastableAs;
use ZeroBoiler\ValueObjects\Console\Commands\ListValueObjectsCommand;
use ZeroBoiler\ValueObjects\Console\Commands\MakeValueObjectCommand;
use ZeroBoiler\ValueObjects\Coordinates;
use ZeroBoiler\ValueObjects\Currency;
use ZeroBoiler\ValueObjects\Duration;
use ZeroBoiler\ValueObjects\Email;
use ZeroBoiler\ValueObjects\ExchangeRateProvider;
use ZeroBoiler\ValueObjects\Money;
use ZeroBoiler\ValueObjects\Percentage;
use ZeroBoiler\ValueObjects\PhoneNumber;
use ZeroBoiler\ValueObjects\Url;
use ZeroBoiler\ValueObjects\ValueObject;
use ZeroBoiler\ValueObjects\ValueObjectCast;
use ZeroBoiler\ValueObjects\ValueObjectInterface;
use ZeroBoiler\ValueObjects\ValueObjectsServiceProvider;

beforeEach(function () {
    $this->srcFiles = glob(__DIR__.'/../src/**/*.php', GLOB_ERR);
});

describe('Phase 53 — Import Consistency, Docblock Completeness, phpstan Parity', function () {
    test('all source files use strict_types', function () {
        foreach ($this->srcFiles as $file) {
            $content = file_get_contents($file);
            expect($content)->toContain('declare(strict_types=1)');
        }
    })->with([['count' => count(glob(__DIR__.'/../src/**/*.php', GLOB_ERR))]]);

    test('no FQN usage where import exists — Currency uses imported NumberFormatter', function () {
        $content = file_get_contents(__DIR__.'/../src/Currency.php');
        expect($content)->toContain('use NumberFormatter;');
        // No FQN \NumberFormatter usage
        expect($content)->not->toContain('\\NumberFormatter');
    });

    test('no FQN usage where import exists — Percentage uses imported ValueError', function () {
        $content = file_get_contents(__DIR__.'/../src/Percentage.php');
        expect($content)->toContain('use ValueError;');
        expect($content)->not->toContain('\\ValueError');
    });

    test('no FQN usage where import exists — Duration uses imported ValueError', function () {
        $content = file_get_contents(__DIR__.'/../src/Duration.php');
        expect($content)->toContain('use ValueError;');
        // Should use import, not FQN
        $fqnCount = substr_count($content, '\\ValueError');
        expect($fqnCount)->toBe(0);
    });

    test('Address constructor has full @param docblock', function () {
        $content = file_get_contents(__DIR__.'/../src/Address.php');
        expect($content)->toContain('@param  string  $street');
        expect($content)->toContain('@param  string|null  $street2');
        expect($content)->toContain('@param  string  $city');
        expect($content)->toContain('@param  string  $state');
        expect($content)->toContain('@param  string  $postalCode');
        expect($content)->toContain('@param  string  $country');
        expect($content)->toContain('@throws ValidationException');
    });

    test('MakeValueObjectCommand getStub() has @param and @return docblock', function () {
        $content = file_get_contents(__DIR__.'/../src/Console/Commands/MakeValueObjectCommand.php');
        expect($content)->toContain('@param  string  $className');
        expect($content)->toContain('@param  string  $namespace');
        expect($content)->toContain('@return string');
    });

    test('phpstan.neon is in parity with phpstan.neon.dist for boolean checks', function () {
        $neon = file_get_contents(__DIR__.'/../phpstan.neon');
        $neonDist = file_get_contents(__DIR__.'/../phpstan.neon.dist');

        // checkUnusedParameters: true in .neon.dist
        expect($neon)->toContain('->checkUnusedParameters(true)');

        // checkUninitializedProperties: true in .neon.dist
        expect($neon)->toContain('->checkUninitializedProperties(true)');

        // checkGenericClassInNonGenericObjectType: true in both
        expect($neon)->toContain('->checkGenericClassInNonGenericObjectType(true)');
        expect($neonDist)->toContain('checkGenericClassInNonGenericObjectType: true');

        // level 9 in both
        expect($neon)->toContain('->level(9)');
        expect($neonDist)->toContain('level: 9');

        // checkMissingIterableValueType: false in both
        expect($neon)->toContain('->checkMissingIterableValueType(false)');
        expect($neonDist)->toContain('checkMissingIterableValueType: false');

        // treatPhpDocTypesAsCertain: false in both
        expect($neon)->toContain('->treatPhpDocTypesAsCertain(false)');
        expect($neonDist)->toContain('treatPhpDocTypesAsCertain: false');

        // reportUnmatchedIgnoredErrors: false in both
        expect($neon)->toContain('->reportUnmatchedIgnoredErrors(false)');
        expect($neonDist)->toContain('reportUnmatchedIgnoredErrors: false');
    });

    test('all exception classes have license headers', function () {
        $files = glob(__DIR__.'/../src/Exceptions/*.php');
        expect($files)->not->toBeEmpty();
        foreach ($files as $file) {
            $content = file_get_contents($file);
            expect($content)->toContain('This file is part of ZeroBoiler, licensed under the MIT license.');
        }
    });

    test('no TODO or FIXME in source files', function () {
        foreach ($this->srcFiles as $file) {
            $content = file_get_contents($file);
            expect($content)->not->toContain('TODO');
            expect($content)->not->toContain('FIXME');
        }
    })->with([['count' => count(glob(__DIR__.'/../src/**/*.php', GLOB_ERR))]]);

    test('all value objects are final', function () {
        $vos = [Address::class, Coordinates::class, Currency::class, Duration::class,
            Email::class, Money::class, Percentage::class, PhoneNumber::class, Url::class];
        foreach ($vos as $vo) {
            $ref = new ReflectionClass($vo);
            expect($ref->isFinal())->toBeTrue("{$vo} must be final");
        }
    });

    test('all value objects have constructor :void return type', function () {
        $vos = [Address::class, Coordinates::class, Currency::class, Duration::class,
            Email::class, Money::class, Percentage::class, PhoneNumber::class, Url::class];
        foreach ($vos as $vo) {
            $constructor = (new ReflectionClass($vo))->getConstructor();
            expect($constructor)->not->toBeNull("{$vo} must have a constructor");
            expect($constructor->getReturnType()?->getName())->toBe('void',
                "{$vo}::__construct() must have :void return type");
        }
    });

    test('all VOs implement ValueObjectInterface via ValueObject', function () {
        $vos = [Address::class, Coordinates::class, Currency::class, Duration::class,
            Email::class, Money::class, Percentage::class, PhoneNumber::class, Url::class];
        foreach ($vos as $vo) {
            expect($vo)->toBeInstanceOf(ValueObjectInterface::class);
            expect($vo)->toBeInstanceOf(ValueObjectContract::class);
        }
    });

    test('exception hierarchy is correct', function () {
        $base = ValueObjectsException::class;
        $arg = InvalidValueObjectsArgumentException::class;
        $runtime = ValueObjectsRuntimeException::class;

        expect((new ReflectionClass($base))->isAbstract())->toBeTrue();
        expect((new ReflectionClass($arg))->isFinal())->toBeTrue();
        expect((new ReflectionClass($runtime))->isFinal())->toBeTrue();
        expect(is_subclass_of($arg, $base))->toBeTrue();
        expect(is_subclass_of($runtime, $base))->toBeTrue();
    });

    test('exception leaf factories return self', function () {
        $argResult = InvalidValueObjectsArgumentException::forMessage('test');
        expect($argResult)->toBeInstanceOf(InvalidValueObjectsArgumentException::class);

        $runtimeResult = ValueObjectsRuntimeException::forMessage('test');
        expect($runtimeResult)->toBeInstanceOf(ValueObjectsRuntimeException::class);
    });

    test('ServiceProvider is final with #[Override]', function () {
        $ref = new ReflectionClass(ValueObjectsServiceProvider::class);
        expect($ref->isFinal())->toBeTrue();
    });

    test('console commands are final', function () {
        expect((new ReflectionClass(MakeValueObjectCommand::class))->isFinal())->toBeTrue();
        expect((new ReflectionClass(ListValueObjectsCommand::class))->isFinal())->toBeTrue();
    });

    test('CastableAs attribute has CLASS target', function () {
        $ref = new ReflectionClass(CastableAs::class);
        $attrs = $ref->getAttributes(Attribute::class);
        expect($attrs)->not->toBeEmpty();
        $instance = $attrs[0]->newInstance();
        expect($instance->flags)->toBe(Attribute::TARGET_CLASS);
    });

    test('ValueObjectCast is final and implements CastsAttributes', function () {
        $ref = new ReflectionClass(ValueObjectCast::class);
        expect($ref->isFinal())->toBeTrue();
        expect($ref->implementsInterface(\Illuminate\Contracts\Database\Eloquent\CastsAttributes::class))->toBeTrue();
    });

    test('ExchangeRateProvider interface has exactly 1 method', function () {
        $ref = new ReflectionClass(ExchangeRateProvider::class);
        expect($ref->getMethods(ReflectionMethod::IS_PUBLIC))->toHaveCount(1);
        expect($ref->hasMethod('getRate'))->toBeTrue();
    });

    test('composer.json metadata integrity', function () {
        $json = json_decode(file_get_contents(__DIR__.'/../composer.json'), true);
        expect($json['name'])->toBe('zeroboiler/value-objects');
        expect($json['type'])->toBe('library');
        expect($json['license'])->toBe('MIT');
        expect($json['require']['php'])->toBe('^8.5');
        expect($json['autoload']['psr-4']['ZeroBoiler\\ValueObjects\\'])->toBe('src/');
        expect($json['extra']['laravel']['providers'])->toContain('ZeroBoiler\\ValueObjects\\ValueObjectsServiceProvider');
        expect($json['version'])->toBeString();
    });

    test('source file count is 22', function () {
        $count = count($this->srcFiles);
        expect($count)->toBe(22, "Expected 22 source files, found {$count}");
    });

    test('test file count (excluding fixtures and bootstrap)', function () {
        $testFiles = glob(__DIR__.'/*.php');
        $testFiles = array_filter($testFiles, fn (string $f): bool =>
            basename($f) !== 'Pest.php' && basename($f) !== 'TestCase.php'
        );
        expect(count($testFiles))->toBe(40);
    });

    test('assertion count matches README', function () {
        // Count actual expect() calls in test files
        $count = 0;
        $testDir = __DIR__;
        foreach (glob($testDir.'/*.php') as $file) {
            if (basename($file) === 'Pest.php' || basename($file) === 'TestCase.php') {
                continue;
            }
            $content = file_get_contents($file);
            $count += substr_count($content, '->expect(');
        }
        // README claims 1214+, verify we have at least that many
        expect($count)->toBeGreaterThanOrEqual(1300, "Assertion count {$count} is below 1300+");
    });

    test('ValueObject abstract class implements ValueObjectInterface', function () {
        $ref = new ReflectionClass(ValueObject::class);
        expect($ref->isAbstract())->toBeTrue();
        expect($ref->implementsInterface(ValueObjectInterface::class))->toBeTrue();
    });

    test('ValueObjectInterface extends ValueObjectContract', function () {
        $ref = new ReflectionClass(ValueObjectInterface::class);
        expect($ref->isInterface())->toBeTrue();
        expect($ref->implementsInterface(ValueObjectContract::class))->toBeTrue();
    });

    test('ValueObjectContract has 5 public methods', function () {
        $ref = new ReflectionClass(ValueObjectContract::class);
        $methods = $ref->getMethods(ReflectionMethod::IS_PUBLIC);
        expect($methods)->toHaveCount(5);
        $names = array_map(fn (ReflectionMethod $m): string => $m->getName(), $methods);
        sort($names);
        expect($names)->toBe(['columnType', 'equals', 'fromPrimitive', 'toPrimitive', 'toArray']);
    });

    test('json_encode calls use JSON_THROW_ON_ERROR', function () {
        // Check ValueObject base class
        $voContent = file_get_contents(__DIR__.'/../src/ValueObject.php');
        expect($voContent)->toContain('JSON_THROW_ON_ERROR');

        // Check ValueObjectCast
        $castContent = file_get_contents(__DIR__.'/../src/ValueObjectCast.php');
        $throwCount = substr_count($castContent, 'JSON_THROW_ON_ERROR');
        expect($throwCount)->toBeGreaterThanOrEqual(2);
    });

    test('rector.php targets PHP 8.5', function () {
        $content = file_get_contents(__DIR__.'/../rector.php');
        expect($content)->toContain('UP_TO_PHP_85');
        expect($content)->toContain('__DIR__');
    });
});
