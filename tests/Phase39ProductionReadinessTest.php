<?php
/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

use ZeroBoiler\ValueObjects\Exceptions\InvalidValueObjectsArgumentException;
use ZeroBoiler\ValueObjects\Exceptions\ValueObjectsException;
use ZeroBoiler\ValueObjects\Exceptions\ValueObjectsRuntimeException;
use ZeroBoiler\ValueObjects\ValueObject;
use ZeroBoiler\ValueObjects\Contracts\ValueObjectContract;
use ZeroBoiler\ValueObjects\Contracts\ValueObjectInterface;
use ZeroBoiler\ValueObjects\ValueObjectsServiceProvider;

describe('Phase 39 — Production Readiness Hardening', function (): void {

    // ─── Exception Hierarchy ─────────────────────────────────────────────

    it('ValueObjectsException is abstract', function (): void {
        expect((new ReflectionClass(ValueObjectsException::class))->isAbstract())->toBeTrue();
    });

    it('ValueObjectsException has :void constructor', function (): void {
        $ref = new ReflectionMethod(ValueObjectsException::class, '__construct');
        expect($ref->getReturnType()?->getName())->toBe('void');
    });

    it('leaf exceptions are final', function (): void {
        $leaves = [
            InvalidValueObjectsArgumentException::class,
            ValueObjectsRuntimeException::class,
        ];

        foreach ($leaves as $leaf) {
            expect((new ReflectionClass($leaf))->isFinal())->toBeTrue(
                "{$leaf} should be final"
            );
        }
    });

    it('ValueObjectsException @see references children', function (): void {
        $doc = (new ReflectionClass(ValueObjectsException::class))->getDocComment();
        expect($doc)->not->toBeFalse();
        expect($doc)->toContain('InvalidValueObjectsArgumentException');
        expect($doc)->toContain('ValueObjectsRuntimeException');
    });

    // ─── Interface Hierarchy ──────────────────────────────────────────────

    it('ValueObjectInterface extends ValueObjectContract', function (): void {
        expect((new ReflectionClass(ValueObjectInterface::class))
            ->getParentClass()->getName())->toBe(ValueObjectContract::class);
    });

    it('ValueObject implements ValueObjectInterface', function (): void {
        expect((new ReflectionClass(ValueObject::class))
            ->implementsInterface(ValueObjectInterface::class))->toBeTrue();
    });

    // ─── Concrete VOs ───────────────────────────────────────────────────

    it('all 9 concrete VOs are final and extend ValueObject', function (): void {
        $voDir = dirname(__DIR__, 2) . '/src';
        $files = glob($voDir . '/*.php');

        $vos = [];
        foreach ($files as $file) {
            $content = file_get_contents($file);
            // Skip abstract classes and interfaces and contracts
            if (str_contains($content, 'abstract class') || str_contains($content, 'interface ')) {
                continue;
            }
            // Find class declarations
            preg_match('/namespace ZeroBoiler\\\\ValueObjects;\s*class (\w+)/', $content, $m);
            if (isset($m[1]) && $m[1] !== 'ValueObject') {
                $vos[] = 'ZeroBoiler\\ValueObjects\\' . $m[1];
            }
        }

        expect(count($vos))->toBeGreaterThanOrEqual(9);

        foreach ($vos as $vo) {
            $ref = new ReflectionClass($vo);
            expect($ref->isFinal())->toBeTrue("{$vo} should be final");
            if ($ref->getParentClass()) {
                // All VOs should extend ValueObject
                expect($ref->getParentClass()->getName())->toBe(ValueObject::class);
            }
        }
    });

    // ─── ServiceProvider ──────────────────────────────────────────────────

    it('ValueObjectsServiceProvider is final', function (): void {
        expect((new ReflectionClass(ValueObjectsServiceProvider::class))->isFinal())->toBeTrue();
    });

    // ─── Version Consistency ────────────────────────────────────────────

    it('composer.json version is 1.45.0', function (): void {
        $composer = json_decode(
            file_get_contents(dirname(__DIR__, 2) . '/composer.json'),
            true,
        );
        expect($composer['version'])->toBe('1.45.0');
    });

    it('README version badge matches composer.json', function (): void {
        $composer = json_decode(
            file_get_contents(dirname(__DIR__, 2) . '/composer.json'),
            true,
        );
        $readme = file_get_contents(dirname(__DIR__, 2) . '/README.md');
        expect($readme)->toContain("version-{$composer['version']}");
    });

    // ─── File Counts ─────────────────────────────────────────────────────

    it('source file count is at least 22', function (): void {
        $srcDir = dirname(__DIR__, 2) . '/src';
        $files = glob($srcDir . '/**/*.php');
        expect(count($files))->toBeGreaterThanOrEqual(22);
    });
});
