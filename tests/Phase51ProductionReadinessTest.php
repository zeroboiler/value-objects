<?php

/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

use ZeroBoiler\ValueObjects\Exceptions\InvalidValueObjectsArgumentException;
use ZeroBoiler\ValueObjects\Exceptions\ValueObjectsException;
use ZeroBoiler\ValueObjects\Exceptions\ValueObjectsRuntimeException;

/**
 * Phase 51 production readiness — exception hierarchy integrity,
 * contract verification, and infrastructure audit for value-objects package.
 */
test('Phase 51: ValueObjectsException is abstract', function (): void {
    $ref = new \ReflectionClass(ValueObjectsException::class);
    expect($ref->isAbstract())->toBeTrue();
    expect($ref->isSubclassOf(\Exception::class))->toBeTrue();
});

test('Phase 51: all leaf exceptions are final', function (): void {
    $leaves = [
        InvalidValueObjectsArgumentException::class,
        ValueObjectsRuntimeException::class,
    ];

    foreach ($leaves as $fqcn) {
        $ref = new \ReflectionClass($fqcn);
        expect($ref->isFinal())->toBeTrue("{$fqcn} must be final");
        expect($ref->isSubclassOf(ValueObjectsException::class))->toBeTrue();
    }
});

test('Phase 51: exception hierarchy FQCN cross-references', function (): void {
    $doc = (new \ReflectionClass(ValueObjectsException::class))->getDocComment();
    expect($doc)->toContain(InvalidValueObjectsArgumentException::class);
    expect($doc)->toContain(ValueObjectsRuntimeException::class);
});

test('Phase 51: composer.json metadata integrity', function (): void {
    $composer = json_decode(file_get_contents(__DIR__.'/../composer.json'), true);

    expect($composer['require']['php'])->toBe('^8.5');
    expect($composer['autoload']['psr-4']['ZeroBoiler\\ValueObjects\\'])->toBe('src/');
    expect($composer['scripts'])->toHaveKeys(['test', 'test:coverage', 'lint', 'lint:fix', 'analyse', 'rector', 'quality']);
});

test('Phase 51: phpstan.neon.dist has required checks', function (): void {
    $content = file_get_contents(__DIR__.'/../phpstan.neon.dist');

    expect($content)->toContain('level: 9');
    expect($content)->toContain('checkUnusedParameters: true');
    expect($content)->toContain('checkUninitializedProperties: true');
    expect($content)->toContain('treatPhpDocTypesAsCertain: false');
});

test('Phase 51: all source files have strict_types and license headers', function (): void {
    $srcDir = __DIR__.'/../src';
    $files = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($srcDir, \FilesystemIterator::SKIP_DOTS),
    );

    $count = 0;
    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }
        $count++;
        $content = file_get_contents($file->getRealPath());
        expect($content)->toContain('declare(strict_types=1)');
        expect($content)->toContain('This file is part of ZeroBoiler, licensed under the MIT license.');
    }

    expect($count)->toBe(22);
});

test('Phase 51: zero TODO or FIXME in source files', function (): void {
    $srcDir = __DIR__.'/../src';
    $files = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($srcDir, \FilesystemIterator::SKIP_DOTS),
    );

    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }
        $content = file_get_contents($file->getRealPath());
        expect($content)->not->toContain('TODO');
        expect($content)->not->toContain('FIXME');
    }
});

test('Phase 51: version consistency', function (): void {
    $composer = json_decode(file_get_contents(__DIR__.'/../composer.json'), true);
    $readme = file_get_contents(__DIR__.'/../README.md');

    expect($readme)->toContain("version-{$composer['version']}");
});

test('Phase 51: project structure files exist', function (): void {
    $required = ['README.md', 'CHANGELOG.md', 'phpstan.neon.dist', 'rector.php', 'composer.json'];

    foreach ($required as $file) {
        expect(file_exists(__DIR__.'/../'.$file))->toBeTrue("Missing: {$file}");
    }
});
