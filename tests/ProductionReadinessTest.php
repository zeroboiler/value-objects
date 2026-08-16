<?php
/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Production Readiness Test Suite
|--------------------------------------------------------------------------
|
| Structural checks to ensure the value-objects package is production-ready.
*/

beforeEach(function (): void {
    $this->srcDir = dirname(__DIR__).'/src';
});

// ── Strict Types ──────────────────────────────────────────────────────

it('all PHP files use declare(strict_types=1)', function (): void {
    $files = glob($this->srcDir.'/**/*.php');
    expect($files)->not->toBeEmpty();

    foreach ($files as $file) {
        $contents = file_get_contents($file);
        expect($contents)
            ->toContain('declare(strict_types=1)', "File {$file} is missing declare(strict_types=1).");
    }
});

// ── Final Classes ────────────────────────────────────────────────────

it('all value objects are final', function (): void {
    $vos = [
        \ZeroBoiler\ValueObjects\Money::class,
        \ZeroBoiler\ValueObjects\Currency::class,
        \ZeroBoiler\ValueObjects\Email::class,
        \ZeroBoiler\ValueObjects\PhoneNumber::class,
        \ZeroBoiler\ValueObjects\Address::class,
        \ZeroBoiler\ValueObjects\Percentage::class,
        \ZeroBoiler\ValueObjects\Duration::class,
        \ZeroBoiler\ValueObjects\Coordinates::class,
        \ZeroBoiler\ValueObjects\Url::class,
    ];

    foreach ($vos as $class) {
        $reflection = new ReflectionClass($class);
        expect($reflection->isFinal())->toBeTrue("{$class} is not marked as final.");
    }
});

it('infrastructure classes are final', function (): void {
    $classes = [
        \ZeroBoiler\ValueObjects\ValueObjectCast::class,
        \ZeroBoiler\ValueObjects\CastableAs::class,
        \ZeroBoiler\ValueObjects\ValueObjectsServiceProvider::class,
        \ZeroBoiler\ValueObjects\Console\Commands\MakeValueObjectCommand::class,
        \ZeroBoiler\ValueObjects\Console\Commands\ListValueObjectsCommand::class,
    ];

    foreach ($classes as $class) {
        if (! class_exists($class)) {
            continue;
        }
        $reflection = new ReflectionClass($class);
        expect($reflection->isFinal())->toBeTrue("{$class} is not marked as final.");
    }
});

it('CastableAs is final readonly', function (): void {
    $reflection = new ReflectionClass(\ZeroBoiler\ValueObjects\CastableAs::class);
    expect($reflection->isFinal())->toBeTrue();
    expect($reflection->isReadOnly())->toBeTrue();
});

// ── Abstract/Base ───────────────────────────────────────────────────

it('ValueObject base class is abstract but NOT final', function (): void {
    $reflection = new ReflectionClass(\ZeroBoiler\ValueObjects\ValueObject::class);
    expect($reflection->isAbstract())->toBeTrue();
    expect($reflection->isFinal())->toBeFalse();
});

// ── Interfaces ───────────────────────────────────────────────────────

it('ValueObjectInterface exists and extends contract', function (): void {
    expect(\ZeroBoiler\ValueObjects\ValueObjectInterface::class)->toBeInterface();
});

it('ExchangeRateProvider interface exists', function (): void {
    expect(\ZeroBoiler\ValueObjects\ExchangeRateProvider::class)->toBeInterface();
});

// ── Traits ───────────────────────────────────────────────────────────

it('Castable is a trait', function (): void {
    expect(\ZeroBoiler\ValueObjects\Castable::class)->toBeTrait();
});

// ── All VOs use Castable ────────────────────────────────────────────

it('all built-in VOs use Castable trait', function (): void {
    $vos = [
        \ZeroBoiler\ValueObjects\Money::class,
        \ZeroBoiler\ValueObjects\Currency::class,
        \ZeroBoiler\ValueObjects\Email::class,
        \ZeroBoiler\ValueObjects\PhoneNumber::class,
        \ZeroBoiler\ValueObjects\Address::class,
        \ZeroBoiler\ValueObjects\Percentage::class,
        \ZeroBoiler\ValueObjects\Duration::class,
        \ZeroBoiler\ValueObjects\Coordinates::class,
        \ZeroBoiler\ValueObjects\Url::class,
    ];

    foreach ($vos as $class) {
        expect(in_array(\ZeroBoiler\ValueObjects\Castable::class, class_uses($class), true))
            ->toBeTrue("{$class} does not use Castable trait.");
    }
});

// ── All VOs extend ValueObject ───────────────────────────────────────

it('all built-in VOs extend ValueObject', function (): void {
    $vos = [
        \ZeroBoiler\ValueObjects\Money::class,
        \ZeroBoiler\ValueObjects\Currency::class,
        \ZeroBoiler\ValueObjects\Email::class,
        \ZeroBoiler\ValueObjects\PhoneNumber::class,
        \ZeroBoiler\ValueObjects\Address::class,
        \ZeroBoiler\ValueObjects\Percentage::class,
        \ZeroBoiler\ValueObjects\Duration::class,
        \ZeroBoiler\ValueObjects\Coordinates::class,
        \ZeroBoiler\ValueObjects\Url::class,
    ];

    foreach ($vos as $class) {
        expect(is_subclass_of($class, \ZeroBoiler\ValueObjects\ValueObject::class))
            ->toBeTrue("{$class} does not extend ValueObject.");
    }
});

// ── No @inheritDoc ───────────────────────────────────────────────────

it('no files contain deprecated @inheritDoc', function (): void {
    $files = glob($this->srcDir.'/**/*.php');
    $stale = [];

    foreach ($files as $file) {
        $contents = file_get_contents($file);

        if (str_contains($contents, '@inheritDoc')) {
            $relativePath = str_replace($this->srcDir.'/', '', $file);
            $stale[] = $relativePath;
        }
    }

    expect($stale)->toBeEmpty(
        'Files using @inheritDoc: '.implode(', ', $stale)
    );
});

// ── No TODO/FIXME ────────────────────────────────────────────────────

it('no TODO or FIXME comments in production code', function (): void {
    $files = glob($this->srcDir.'/**/*.php');
    $todos = [];

    foreach ($files as $file) {
        $contents = file_get_contents($file);
        $lines = explode("\n", $contents);

        foreach ($lines as $num => $line) {
            if (preg_match('/\/\/\s*(TODO|FIXME|HACK|XXX)/i', $line)) {
                $relativePath = str_replace($this->srcDir.'/', '', $file);
                $todos[] = "{$relativePath}:{$num}";
            }
        }
    }

    expect($todos)->toBeEmpty(
        'TODO/FIXME comments found: '.implode(', ', $todos)
    );
});

// ── Composer.json Validation ─────────────────────────────────────────

it('composer.json has correct minimum-stability', function (): void {
    $composer = json_decode(file_get_contents(dirname(__DIR__).'/composer.json'), true);
    expect($composer['minimum-stability'])->toBe('stable');
});

it('composer.json has sort-packages enabled', function (): void {
    $composer = json_decode(file_get_contents(dirname(__DIR__).'/composer.json'), true);
    expect($composer['config']['sort-packages'])->toBe(true);
});

it('composer.json requires PHP 8.5+', function (): void {
    $composer = json_decode(file_get_contents(dirname(__DIR__).'/composer.json'), true);
    expect($composer['require']['php'])->toStartWith('^8.5');
});

it('composer.json has MIT license', function (): void {
    $composer = json_decode(file_get_contents(dirname(__DIR__).'/composer.json'), true);
    expect($composer['license'])->toBe('MIT');
});

it('composer.json has version key', function (): void {
    $composer = json_decode(file_get_contents(dirname(__DIR__).'/composer.json'), true);
    expect($composer)->toHaveKey('version');
    expect($composer['version'])->toMatch('/^\d+\.\d+\.\d+$/');
});

// ── File Count ────────────────────────────────────────────────────────

it('has at least 15 source files', function (): void {
    $files = glob($this->srcDir.'/**/*.php');
    expect(count($files))->toBeGreaterThanOrEqual(15);
});

it('has at least 10 test files', function (): void {
    $testFiles = glob(dirname(__DIR__).'/tests/*Test.php');
    expect(count($testFiles))->toBeGreaterThanOrEqual(10);
});
