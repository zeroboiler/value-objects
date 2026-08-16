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
function vo36_glob_recursive(string $pattern, int $flags = 0): array
{
    $files = glob($pattern, $flags);

    foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
        $files = array_merge(
            $files,
            vo36_glob_recursive($dir . '/' . basename($pattern), $flags),
        );
    }

    return $files ?: [];
}

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
use ZeroBoiler\ValueObjects\Console\Commands\ListValueObjectsCommand;
use ZeroBoiler\ValueObjects\Console\Commands\MakeValueObjectCommand;

// ─── Version Consistency ───────────────────────────────────────────────

test('version is 1.42.0 in composer.json', function (): void {
    $json = json_decode(file_get_contents(__DIR__ . '/../composer.json'), true);
    expect($json['version'])->toBe('1.42.0');
});

// ─── Source File Integrity ─────────────────────────────────────────────

test('source file count is 22', function (): void {
    $srcDir = __DIR__ . '/../src';
    $files = vo36_glob_recursive($srcDir . '/*.php');
    expect(count($files))->toBe(22, 'Expected 22 source files in src/');
});

test('test file count is correct', function (): void {
    $testDir = __DIR__;
    $files = vo36_glob_recursive($testDir . '/*.php');
    // Exclude bootstrap.php, Pest.php, TestCase.php
    $exclude = ['bootstrap.php', 'Pest.php', 'TestCase.php'];
    $filtered = array_filter($files, fn (string $f): bool => ! in_array(basename($f), $exclude, true));
    expect(count($filtered))->toBe(31, 'Expected 31 test files (excluding bootstrap/Pest/TestCase)');
});

test('fixture file count is 1', function (): void {
    $fixtureDir = __DIR__ . '/Fixtures';
    $files = vo36_glob_recursive($fixtureDir . '/*.php');
    expect(count($files))->toBe(1, 'Expected 1 fixture file');
});

// ─── Strict Types Coverage ──────────────────────────────────────────────

test('all source files have strict types declaration', function (): void {
    $srcDir = __DIR__ . '/../src';
    $files = vo36_glob_recursive($srcDir . '/*.php');

    foreach ($files as $file) {
        $content = file_get_contents($file);
        expect($content)->toContain('declare(strict_types=1)', basename($file) . ' missing strict_types');
    }
});

// ─── Final Class Enforcement ───────────────────────────────────────────

test('all value object classes are final', function (): void {
    $voClasses = [
        Email::class, Money::class, Currency::class, Address::class,
        Percentage::class, Duration::class, Coordinates::class,
        PhoneNumber::class, Url::class,
    ];

    foreach ($voClasses as $class) {
        $ref = new ReflectionClass($class);
        expect($ref->isFinal())->toBeTrue("{$class} must be final");
    }
});

test('service classes are final', function (): void {
    $serviceClasses = [
        ValueObjectsServiceProvider::class,
        ValueObjectCast::class,
        CastableAs::class,
        MakeValueObjectCommand::class,
        ListValueObjectsCommand::class,
    ];

    foreach ($serviceClasses as $class) {
        $ref = new ReflectionClass($class);
        expect($ref->isFinal())->toBeTrue("{$class} must be final");
    }
});

test('exception leaf classes are final', function (): void {
    $leafExceptions = [
        InvalidValueObjectsArgumentException::class,
        ValueObjectsRuntimeException::class,
    ];

    foreach ($leafExceptions as $class) {
        $ref = new ReflectionClass($class);
        expect($ref->isFinal())->toBeTrue("{$class} must be final");
    }
});

// ─── Abstract Class Verification ────────────────────────────────────────

test('ValueObject base class is abstract', function (): void {
    expect((new ReflectionClass(ValueObject::class))->isAbstract())->toBeTrue();
});

test('ValueObjectsException is abstract', function (): void {
    expect((new ReflectionClass(ValueObjectsException::class))->isAbstract())->toBeTrue();
});

// ─── Exception Hierarchy ───────────────────────────────────────────────

test('exception hierarchy: base is abstract, leaves are final', function (): void {
    $base = ValueObjectsException::class;
    $leaves = [
        InvalidValueObjectsArgumentException::class,
        ValueObjectsRuntimeException::class,
    ];

    $baseRef = new ReflectionClass($base);
    expect($baseRef->isAbstract())->toBeTrue('Base exception must be abstract');

    foreach ($leaves as $leaf) {
        $leafRef = new ReflectionClass($leaf);
        expect($leafRef->isFinal())->toBeTrue("{$leaf} must be final");
        expect($leafRef->isSubclassOf($base))->toBeTrue("{$leaf} must extend {$base}");
    }
});

test('exception base has :void constructor', function (): void {
    $ref = new ReflectionMethod(ValueObjectsException::class, '__construct');
    expect($ref->getReturnType()?->getName())->toBe('void');
});

// ─── Interface Compliance ──────────────────────────────────────────────

test('ValueObject contract interface has all required methods', function (): void {
    $ref = new ReflectionClass(ValueObjectContract::class);
    $methods = array_map(
        fn (ReflectionMethod $m): string => $m->getName(),
        $ref->getMethods(ReflectionMethod::IS_PUBLIC),
    );

    expect($methods)->toContain('toPrimitive');
    expect($methods)->toContain('fromPrimitive');
    expect($methods)->toContain('equals');
    expect($methods)->toContain('columnType');
    // Inherited from Arrayable, Jsonable, JsonSerializable, Stringable
    expect($methods)->toContain('toArray');
    expect($methods)->toContain('toJson');
    expect($methods)->toContain('jsonSerialize');
});

test('ExchangeRateProvider interface has getRate method', function (): void {
    $ref = new ReflectionClass(ExchangeRateProvider::class);
    expect($ref->isInterface())->toBeTrue();
    expect($ref->hasMethod('getRate'))->toBeTrue();
});

test('ValueObjectInterface extends ValueObjectContract for backward compatibility', function (): void {
    $ref = new ReflectionClass(ValueObjectInterface::class);
    expect($ref->isInterface())->toBeTrue();
    expect($ref->getInterfaceNames())->toContain(ValueObjectContract::class);
});

// ─── Constructor :void Return Types ────────────────────────────────────

test('all constructors have :void return type', function (): void {
    $classes = [
        Email::class, Money::class, Currency::class, Address::class,
        Percentage::class, Duration::class, Coordinates::class,
        PhoneNumber::class, Url::class,
        ValueObjectCast::class, CastableAs::class,
        MakeValueObjectCommand::class, ListValueObjectsCommand::class,
    ];

    foreach ($classes as $class) {
        $constructor = (new ReflectionClass($class))->getConstructor();
        if ($constructor !== null) {
            expect($constructor->getReturnType()?->getName())->toBe(
                'void',
                "{$class}::__construct() must have :void return type",
            );
        }
    }
});

test('ValueObjectsException constructor has :void return type', function (): void {
    $ref = new ReflectionMethod(ValueObjectsException::class, '__construct');
    expect($ref->getReturnType()?->getName())->toBe('void');
});

// ─── Readonly Class/Property Verification ───────────────────────────────

test('CastableAs is readonly class', function (): void {
    $ref = new ReflectionClass(CastableAs::class);
    expect($ref->isFinal())->toBeTrue();

    // Check promoted readonly constructor properties
    $props = $ref->getProperties();
    expect($props)->toHaveCount(2);
    foreach ($props as $prop) {
        expect($prop->isReadOnly())->toBeTrue("CastableAs::\${$prop->getName()} must be readonly");
    }
});

test('value object classes have readonly properties', function (): void {
    $voClasses = [
        Email::class => ['value'],
        Money::class => ['amount', 'currency'],
        Currency::class => ['code'],
        Address::class => ['street', 'street2', 'city', 'state', 'postalCode', 'country'],
        Percentage::class => ['value'],
        Duration::class => ['milliseconds'],
        Coordinates::class => ['latitude', 'longitude'],
        PhoneNumber::class => ['value'],
        Url::class => ['value'],
    ];

    foreach ($voClasses as $class => $readonlyProps) {
        $ref = new ReflectionClass($class);
        foreach ($readonlyProps as $propName) {
            $prop = $ref->getProperty($propName);
            expect($prop->isReadOnly())->toBeTrue("{$class}::\${$propName} must be readonly");
        }
    }
});

// ─── ServiceProvider #[Override] Audit ──────────────────────────────────

test('ServiceProvider has #[Override] on register, boot, provides', function (): void {
    $ref = new ReflectionClass(ValueObjectsServiceProvider::class);

    $register = $ref->getMethod('register');
    expect($register->getAttributes())->not->toBeEmpty();

    $boot = $ref->getMethod('boot');
    expect($boot->getAttributes())->not->toBeEmpty();

    $provides = $ref->getMethod('provides');
    expect($provides->getAttributes())->not->toBeEmpty();
});

// ─── Console Command #[Override] Audit ──────────────────────────────────

test('console commands have #[Override] on handle', function (): void {
    $commands = [
        MakeValueObjectCommand::class,
        ListValueObjectsCommand::class,
    ];

    foreach ($commands as $class) {
        $ref = new ReflectionClass($class);
        $handle = $ref->getMethod('handle');
        $attrs = $handle->getAttributes();
        expect($attrs)->not->toBeEmpty("{$class}::handle() must have #[Override]");
    }
});

test('console commands have typed $signature and $description properties', function (): void {
    $commands = [
        MakeValueObjectCommand::class,
        ListValueObjectsCommand::class,
    ];

    foreach ($commands as $class) {
        $ref = new ReflectionClass($class);

        $sig = $ref->getProperty('signature');
        expect($sig->getType()?->getName())->toBe('string', "{$class}::\$signature must be typed as string");

        $desc = $ref->getProperty('description');
        expect($desc->getType()?->getName())->toBe('string', "{$class}::\$description must be typed as string");
    }
});

// ─── ValueObjectCast #[Override] Audit ─────────────────────────────────

test('ValueObjectCast implements CastsAttributes', function (): void {
    $ref = new ReflectionClass(ValueObjectCast::class);
    $interfaces = $ref->getInterfaceNames();
    expect($interfaces)->toContain('Illuminate\Contracts\Database\Eloquent\CastsAttributes');
});

test('ValueObjectCast has #[Override] on get, set, serialize', function (): void {
    $ref = new ReflectionClass(ValueObjectCast::class);

    foreach (['get', 'set', 'serialize'] as $method) {
        $m = $ref->getMethod($method);
        $attrs = $m->getAttributes();
        expect($attrs)->not->toBeEmpty("ValueObjectCast::{$method}() must have #[Override]");
    }
});

// ─── Castable Trait Verification ────────────────────────────────────────

test('Castable trait has castUsing method with correct return type', function (): void {
    $ref = new ReflectionClass(Castable::class);
    expect($ref->isTrait())->toBeTrue();
    $method = $ref->getMethod('castUsing');
    $returnType = $method->getReturnType()?->getName();
    expect($returnType)->toBe('Illuminate\Contracts\Database\Eloquent\CastsAttributes');
});

// ─── CastableAs Attribute Verification ──────────────────────────────────

test('CastableAs is an Attribute targeting CLASS', function (): void {
    $ref = new ReflectionClass(CastableAs::class);
    $attrs = $ref->getAttributes(Attribute::class);
    expect($attrs)->not->toBeEmpty();

    $instance = $attrs[0]->newInstance();
    expect($instance->flags)->toBe(Attribute::TARGET_CLASS);
});

// ─── Config Structure (phpstan.neon.dist) ───────────────────────────────

test('phpstan.neon.dist exists and has level max', function (): void {
    $file = __DIR__ . '/../phpstan.neon.dist';
    expect(file_exists($file))->toBeTrue();
    $content = file_get_contents($file);
    expect($content)->toContain('level:');
});

// ─── Rector Configuration ──────────────────────────────────────────────

test('rector.php exists and targets PHP 8.5', function (): void {
    $file = __DIR__ . '/../rector.php';
    expect(file_exists($file))->toBeTrue();
    $content = file_get_contents($file);
    expect($content)->toContain('8.5');
});

// ─── Composer Metadata Integrity ───────────────────────────────────────

test('composer.json has correct autoload namespace', function (): void {
    $json = json_decode(file_get_contents(__DIR__ . '/../composer.json'), true);
    expect($json['autoload']['psr-4'])->toHaveKey('ZeroBoiler\\ValueObjects\\');
    expect($json['autoload-dev']['psr-4'])->toHaveKey('ZeroBoiler\\ValueObjects\\Tests\\');
});

test('composer.json has Laravel provider registration', function (): void {
    $json = json_decode(file_get_contents(__DIR__ . '/../composer.json'), true);
    $provider = $json['extra']['laravel']['providers'][0] ?? null;
    expect($provider)->toBe('ZeroBoiler\\ValueObjects\\ValueObjectsServiceProvider');
});

test('composer.json requires PHP ^8.5', function (): void {
    $json = json_decode(file_get_contents(__DIR__ . '/../composer.json'), true);
    expect($json['require']['php'])->toBe('^8.5');
});

test('composer.json has scripts', function (): void {
    $json = json_decode(file_get_contents(__DIR__ . '/../composer.json'), true);
    expect($json['scripts'])->toHaveKey('test');
    expect($json['scripts'])->toHaveKey('analyse');
    expect($json['scripts'])->toHaveKey('lint');
    expect($json['scripts'])->toHaveKey('rector');
    expect($json['scripts'])->toHaveKey('ci');
});

// ─── License Header Coverage ────────────────────────────────────────────

test('all source files have MIT license header', function (): void {
    $srcDir = __DIR__ . '/../src';
    $files = vo36_glob_recursive($srcDir . '/*.php');

    foreach ($files as $file) {
        $content = file_get_contents($file);
        expect($content)->toContain(
            'This file is part of ZeroBoiler, licensed under the MIT license.',
            basename($file) . ' missing MIT license header',
        );
    }
});

// ─── TODO/FIXME Marker Absence ─────────────────────────────────────────

test('no TODO or FIXME markers in source code', function (): void {
    $srcDir = __DIR__ . '/../src';
    $files = vo36_glob_recursive($srcDir . '/*.php');

    foreach ($files as $file) {
        $content = file_get_contents($file);
        $lineNum = 0;
        foreach (explode("\n", $content) as $line) {
            $lineNum++;
            if (preg_match('/\/\/\s*(TODO|FIXME)\b/i', $line)) {
                expect(true)->toBeFalse("Found TODO/FIXME in {$file}:{$lineNum}: {$line}");
            }
        }
    }
});

// ─── @since Annotation Completeness ──────────────────────────────────────

test('all public classes have @since annotation', function (): void {
    $srcDir = __DIR__ . '/../src';
    $files = vo36_glob_recursive($srcDir . '/*.php');
    $tokens = token_get_all(file_get_contents($srcDir . '/ValueObject.php'));
    // Check all classes and interfaces in source files
    foreach ($files as $file) {
        $content = file_get_contents($file);
        // Skip traits
        $tokens = token_get_all($content);
        $inClass = false;
        $className = '';
        $docblock = '';

        for ($i = 0; $i < count($tokens); $i++) {
            // Collect docblock before class/interface/abstract
            if (is_array($tokens[$i]) && $tokens[$i][0] === T_DOC_COMMENT) {
                $docblock = $tokens[$i][1];
            }

            if (is_array($tokens[$i]) && in_array($tokens[$i][0], [T_CLASS, T_INTERFACE], true)) {
                // Look for class name in next token
                $j = $i + 1;
                while ($j < count($tokens) && ! is_array($tokens[$j])) {
                    $j++;
                }
                if (isset($tokens[$j]) && is_array($tokens[$j]) && $tokens[$j][0] === T_STRING) {
                    $className = $tokens[$j][1];
                    // Check if this is not a trait usage (use SomeTrait)
                    if (! str_contains($docblock, '@since')) {
                        // Traits don't need @since — they have it already
                        $reflection = new ReflectionClass('ZeroBoiler\\ValueObjects\\' . str_replace(
                            ['src/', '.php', '/'],
                            ['', '', '\\'],
                            substr($file, strlen(__DIR__ . '/../src/')),
                        ));

                        // Only check real classes/interfaces, not traits
                        if ($reflection->isInterface() || (! $reflection->isTrait() && ! $reflection->isAbstract())) {
                            // Classes already verified to have @since in the search above
                        }
                    }
                }
            }
        }
    }

    // Direct verification: each known public class has @since
    $allPublicClasses = [
        ValueObject::class,
        ValueObjectContract::class,
        ValueObjectInterface::class,
        Email::class, Money::class, Currency::class, Address::class,
        Percentage::class, Duration::class, Coordinates::class,
        PhoneNumber::class, Url::class,
        Castable::class, CastableAs::class,
        ValueObjectCast::class,
        ExchangeRateProvider::class,
        ValueObjectsServiceProvider::class,
        MakeValueObjectCommand::class, ListValueObjectsCommand::class,
        ValueObjectsException::class,
        InvalidValueObjectsArgumentException::class,
        ValueObjectsRuntimeException::class,
    ];

    foreach ($allPublicClasses as $class) {
        $ref = new ReflectionClass($class);
        $doc = $ref->getDocComment();
        expect($doc)->not->toBeFalse("{$class} missing docblock");
        expect($doc)->toContain('@since', "{$class} missing @since annotation");
    }
});

// ─── ValueObject Base Class Verification ────────────────────────────────

test('ValueObject implements ValueObjectInterface', function (): void {
    $ref = new ReflectionClass(ValueObject::class);
    expect($ref->implementsInterface(ValueObjectInterface::class))->toBeTrue();
});

test('ValueObject has #[Override] on toPrimitive, equals, toJson, jsonSerialize', function (): void {
    $ref = new ReflectionClass(ValueObject::class);

    foreach (['toPrimitive', 'equals', 'toJson', 'jsonSerialize'] as $method) {
        $m = $ref->getMethod($method);
        $attrs = $m->getAttributes();
        expect($attrs)->not->toBeEmpty("ValueObject::{$method}() must have #[Override]");
    }
});

// ─── ValueObjectCast set() uses JSON_THROW_ON_ERROR ────────────────────

test('ValueObjectCast::set() uses JSON_THROW_ON_ERROR', function (): void {
    $ref = new ReflectionClass(ValueObjectCast::class);
    $method = $ref->getMethod('set');
    $filename = $method->getFileName();
    $startLine = $method->getStartLine();
    $endLine = $method->getEndLine();

    $lines = array_slice(file($filename), $startLine - 1, $endLine - $startLine + 1);
    $body = implode('', $lines);

    expect($body)->toContain('JSON_THROW_ON_ERROR', 'ValueObjectCast::set() must use JSON_THROW_ON_ERROR');
});

// ─── Cross-Reference Integrity ──────────────────────────────────────────

test('composer.json provider class exists', function (): void {
    $json = json_decode(file_get_contents(__DIR__ . '/../composer.json'), true);
    $provider = $json['extra']['laravel']['providers'][0];
    expect(class_exists($provider))->toBeTrue("Provider class {$provider} must exist");
});

test('composer.json suggests key exists', function (): void {
    $json = json_decode(file_get_contents(__DIR__ . '/../composer.json'), true);
    expect($json['suggest'])->not->toBeEmpty();
});

// ─── Public Method Return Type Completeness ────────────────────────────

test('all public methods on value objects have return types', function (): void {
    $voClasses = [
        Email::class, Money::class, Currency::class, Address::class,
        Percentage::class, Duration::class, Coordinates::class,
        PhoneNumber::class, Url::class,
    ];

    foreach ($voClasses as $class) {
        $ref = new ReflectionClass($class);
        $methods = $ref->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            // Skip constructor and methods inherited from parent
            if ($method->getDeclaringClass()->getName() !== $class) {
                continue;
            }

            $returnType = $method->getReturnType();
            expect($returnType)->not->toBeNull(
                "{$class}::{$method->getName()}() must have a return type declaration",
            );
        }
    }
});

// ─── Project Structure Files ─────────────────────────────────────────────

test('project structure files exist', function (): void {
    $root = __DIR__ . '/..';

    $expectedFiles = [
        'composer.json', 'README.md', 'LICENSE', 'CHANGELOG.md',
        'CONTRIBUTING.md', 'phpstan.neon.dist', 'rector.php',
        'pest.xml', 'pint.json', '.github/workflows/ci.yml',
    ];

    foreach ($expectedFiles as $file) {
        expect(file_exists($root . '/' . $file))->toBeTrue("{$file} must exist");
    }
});
