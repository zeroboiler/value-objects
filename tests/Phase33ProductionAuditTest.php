<?php
/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

use ZeroBoiler\ValueObjects\Exceptions\InvalidValueObjectsArgumentException;
use ZeroBoiler\ValueObjects\Exceptions\ValueObjectsException;
use ZeroBoiler\ValueObjects\Exceptions\ValueObjectsRuntimeException;
use ZeroBoiler\ValueObjects\ValueObjectsServiceProvider;

describe('Phase 33 Production Audit', function (): void {

    // ─── Test File Counts ──────────────────────────────────────────────

    test('test file count is at least 27', function (): void {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(__DIR__, RecursiveDirectoryIterator::SKIP_DOTS),
        );
        foreach ($iterator as $file) {
            if ($file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }
        $files = array_filter($files, fn (string $f): bool =>
            ! str_contains($f, 'Fixtures') &&
            basename($f) !== 'bootstrap.php' &&
            basename($f) !== 'Pest.php' &&
            basename($f) !== 'TestCase.php'
        );
        expect(count($files))->toBeGreaterThanOrEqual(27);
    });

    // ─── Source File Count ──────────────────────────────────────────────

    test('source file count is 22', function (): void {
        $count = 0;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(__DIR__.'/../src', RecursiveDirectoryIterator::SKIP_DOTS),
        );
        foreach ($iterator as $file) {
            if ($file->getExtension() === 'php') {
                $count++;
            }
        }
        expect($count)->toBe(22);
    });

    // ─── Version Consistency ────────────────────────────────────────────

    test('composer.json version is 1.39.0', function (): void {
        $json = json_decode(file_get_contents(__DIR__.'/../composer.json'), true);
        expect($json['version'])->toBe('1.39.0');
    });

    // ─── Exception Hierarchy ────────────────────────────────────────────

    test('ValueObjectsException is abstract', function (): void {
        expect((new ReflectionClass(ValueObjectsException::class))->isAbstract())->toBeTrue();
    });

    test('all leaf exceptions are final', function (): void {
        $leaves = [
            InvalidValueObjectsArgumentException::class,
            ValueObjectsRuntimeException::class,
        ];
        foreach ($leaves as $leaf) {
            expect((new ReflectionClass($leaf))->isFinal())->toBeTrue("{$leaf} should be final");
        }
    });

    test('all leaf exceptions extend ValueObjectsException', function (): void {
        $leaves = [
            InvalidValueObjectsArgumentException::class,
            ValueObjectsRuntimeException::class,
        ];
        foreach ($leaves as $leaf) {
            expect(is_subclass_of($leaf, ValueObjectsException::class))->toBeTrue();
        }
    });

    test('ValueObjectsException constructor has :void return type', function (): void {
        $ctor = (new ReflectionClass(ValueObjectsException::class))->getConstructor();
        expect($ctor)->not->toBeNull();
        expect($ctor->getReturnType()->getName())->toBe('void');
    });

    // ─── All Source Files Have strict_types ─────────────────────────────

    test('all source files have declare(strict_types=1)', function (): void {
        $missing = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(__DIR__.'/../src', RecursiveDirectoryIterator::SKIP_DOTS),
        );
        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $content = file_get_contents($file->getPathname());
            if ($content === false || ! str_contains($content, 'declare(strict_types=1)')) {
                $missing[] = $file->getPathname();
            }
        }
        expect($missing)->toBeEmpty('Missing strict_types: '.implode(', ', $missing));
    });

    // ─── Zero TODO/FIXME ────────────────────────────────────────────────

    test('no TODO markers in source', function (): void {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(__DIR__.'/../src', RecursiveDirectoryIterator::SKIP_DOTS),
        );
        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $content = file_get_contents($file->getPathname());
            if ($content !== false && str_contains($content, 'TODO')) {
                $files[] = $file->getPathname();
            }
        }
        expect($files)->toBeEmpty('Found TODO in: '.implode(', ', $files));
    });

    // ─── ServiceProvider ───────────────────────────────────────────────

    test('ServiceProvider has #[Override] on register/boot', function (): void {
        $ref = new ReflectionClass(ValueObjectsServiceProvider::class);
        foreach (['register', 'boot'] as $method) {
            $m = $ref->getMethod($method);
            $attrs = array_map(fn ($a): string => $a->getName(), $m->getAttributes());
            expect($attrs)->toContain('Override');
        }
    });

    test('ServiceProvider provides() returns non-empty', function (): void {
        $provider = new ValueObjectsServiceProvider(app());
        $services = $provider->provides();
        expect($services)->not->toBeEmpty();
    });
});
