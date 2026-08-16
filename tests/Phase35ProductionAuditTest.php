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
function vo_glob_recursive(string $pattern, int $flags = 0): array
{
    $files = glob($pattern, $flags);

    foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
        $files = array_merge(
            $files,
            vo_glob_recursive($dir . '/' . basename($pattern), $flags),
        );
    }

    return $files ?: [];
}

use ZeroBoiler\ValueObjects\Exceptions\ValueObjectsException;
use ZeroBoiler\ValueObjects\Contracts\ValueObject;

test('version is 1.42.0', function (): void {
    $json = json_decode(file_get_contents(__DIR__ . '/../composer.json'), true);
    expect($json['version'])->toBe('1.42.0');
});

test('all source files have strict types declaration', function (): void {
    $srcDir = __DIR__ . '/../src';
    $files = vo_glob_recursive($srcDir . '/*.php');

    expect($files)->not->toBeEmpty();
    foreach ($files as $file) {
        $content = file_get_contents($file);
        expect($content)->toContain('declare(strict_types=1)');
    }
});

test('no redundant same-namespace imports', function (): void {
    $srcDir = __DIR__ . '/../src';
    $files = vo_glob_recursive($srcDir . '/*.php');

    foreach ($files as $file) {
        $content = file_get_contents($file);

        // Extract namespace
        if (! preg_match('/^namespace ([^;]+);/m', $content, $m)) {
            continue;
        }
        $namespace = trim($m[1]);

        // Check all use statements
        preg_match_all('/^use ([^;]+);/m', $content, $uses);
        foreach ($uses[1] as $use) {
            $use = trim($use);
            // Skip aliases (use X as Y)
            $importedClass = preg_replace('/\s+as\s+\w+$/', '', $use);
            if (str_starts_with($importedClass, $namespace . '\\')) {
                // This is importing from own namespace — redundant
                $basename = substr($importedClass, strlen($namespace) + 1);
                expect($basename)->toBeEmpty(
                    basename($file) . ' has redundant same-namespace import: ' . $use
                );
            }
        }
    }
});

test('ValueObjectsException is abstract', function (): void {
    expect((new ReflectionClass(ValueObjectsException::class))->isAbstract())->toBeTrue();
});

test('ValueObject interface exists', function (): void {
    expect(interface_exists(ValueObject::class))->toBeTrue();
    expect((new ReflectionClass(ValueObject::class))->isInterface())->toBeTrue();
});

test('all source files have MIT license header', function (): void {
    $srcDir = __DIR__ . '/../src';
    $files = vo_glob_recursive($srcDir . '/*.php');

    foreach ($files as $file) {
        $content = file_get_contents($file);
        expect($content)
            ->toContain('This file is part of ZeroBoiler, licensed under the MIT license.');
    }
});

test('source file count matches expected', function (): void {
    $srcDir = __DIR__ . '/../src';
    $files = vo_glob_recursive($srcDir . '/*.php');
    expect(count($files))->toBe(22, 'Expected 22 source files');
});
