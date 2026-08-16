<?php
/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

use ZeroBoiler\ValueObjects\ValueObjectsServiceProvider;
use ZeroBoiler\ValueObjects\ValueObject;
use ZeroBoiler\ValueObjects\ValueObjectCast;
use ZeroBoiler\ValueObjects\Contracts\ValueObject as ValueObjectContract;
use ZeroBoiler\ValueObjects\Exceptions\InvalidValueObjectsArgumentException;
use ZeroBoiler\ValueObjects\Exceptions\ValueObjectsException;
use ZeroBoiler\ValueObjects\Exceptions\ValueObjectsRuntimeException;
use ZeroBoiler\ValueObjects\Address;
use ZeroBoiler\ValueObjects\Castable;
use ZeroBoiler\ValueObjects\CastableAs;
use ZeroBoiler\ValueObjects\Coordinates;
use ZeroBoiler\ValueObjects\Currency;
use ZeroBoiler\ValueObjects\Duration;
use ZeroBoiler\ValueObjects\Email;
use ZeroBoiler\ValueObjects\ExchangeRateProvider;
use ZeroBoiler\ValueObjects\Money;
use ZeroBoiler\ValueObjects\Percentage;
use ZeroBoiler\ValueObjects\PhoneNumber;
use ZeroBoiler\ValueObjects\Url;
use ZeroBoiler\ValueObjects\Console\Commands\ListValueObjectsCommand;
use ZeroBoiler\ValueObjects\Console\Commands\MakeValueObjectCommand;

/**
 * Phase 45 Production Readiness Test
 *
 * Comprehensive audit of the value-objects package.
 */
describe('Phase 45 Production Readiness', function (): void {
    describe('Source File Inventory', function (): void {
        it('has 22 source files', function (): void {
            $srcDir = __DIR__ . '/../src';
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($srcDir, RecursiveDirectoryIterator::SKIP_DOTS),
            );
            $phpFiles = [];
            foreach ($iterator as $file) {
                if ($file->getExtension() === 'php') {
                    $phpFiles[] = $file->getRealPath();
                }
            }
            expect(count($phpFiles))->toBe(22);
        });

        it('has strict_types in all source files', function (): void {
            $srcDir = __DIR__ . '/../src';
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($srcDir, RecursiveDirectoryIterator::SKIP_DOTS),
            );
            $missing = [];
            foreach ($iterator as $file) {
                if ($file->getExtension() === 'php') {
                    $content = file_get_contents($file->getRealPath());
                    if (! str_contains($content, 'declare(strict_types=1)')) {
                        $missing[] = $file->getFilename();
                    }
                }
            }
            expect($missing)->toBeEmpty();
        });

        it('has license header in all source files', function (): void {
            $srcDir = __DIR__ . '/../src';
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($srcDir, RecursiveDirectoryIterator::SKIP_DOTS),
            );
            $missing = [];
            foreach ($iterator as $file) {
                if ($file->getExtension() === 'php') {
                    $content = file_get_contents($file->getRealPath());
                    if (! str_contains($content, 'This file is part of ZeroBoiler')) {
                        $missing[] = $file->getFilename();
                    }
                }
            }
            expect($missing)->toBeEmpty();
        });

        it('has zero TODO/FIXME markers', function (): void {
            $srcDir = __DIR__ . '/../src';
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($srcDir, RecursiveDirectoryIterator::SKIP_DOTS),
            );
            $markers = [];
            foreach ($iterator as $file) {
                if ($file->getExtension() === 'php') {
                    $content = file_get_contents($file->getRealPath());
                    foreach (['TODO', 'FIXME', 'HACK', 'XXX'] as $marker) {
                        if (str_contains($content, $marker)) {
                            $markers[] = $file->getFilename() . ':' . $marker;
                        }
                    }
                }
            }
            expect($markers)->toBeEmpty();
        });
    });

    describe('Final Class Enforcement', function (): void {
        $finalClasses = [
            ValueObjectsServiceProvider::class,
            ValueObjectCast::class,
            Address::class,
            Coordinates::class,
            Currency::class,
            Duration::class,
            Email::class,
            ExchangeRateProvider::class,
            Money::class,
            Percentage::class,
            PhoneNumber::class,
            Url::class,
            ListValueObjectsCommand::class,
            MakeValueObjectCommand::class,
        ];

        it('enforces final on all ' . count($finalClasses) . ' classes', function () use ($finalClasses): void {
            foreach ($finalClasses as $class) {
                $ref = new ReflectionClass($class);
                expect($ref->isFinal())->toBeTrue($class . ' must be final');
            }
        });
    });

    describe('Exception Hierarchy', function (): void {
        it('ValueObjectsException is abstract', function (): void {
            expect((new ReflectionClass(ValueObjectsException::class))->isAbstract())->toBeTrue();
        });

        it('ValueObjectsException has :void constructor', function (): void {
            $ctor = (new ReflectionClass(ValueObjectsException::class))->getConstructor();
            expect($ctor->getReturnType()->getName())->toBe('void');
        });

        it('has 2 final leaf exceptions', function (): void {
            foreach ([InvalidValueObjectsArgumentException::class, ValueObjectsRuntimeException::class] as $class) {
                $ref = new ReflectionClass($class);
                expect($ref->isFinal())->toBeTrue($class . ' must be final');
                expect($ref->isSubclassOf(ValueObjectsException::class))->toBeTrue();
            }
        });
    });

    describe('ValueObject Base Class', function (): void {
        it('ValueObject implements ValueObjectContract', function (): void {
            expect((new ReflectionClass(ValueObject::class))->implementsInterface(ValueObjectContract::class))->toBeTrue();
        });
    });

    describe('ServiceProvider Contract', function (): void {
        it('provides empty array (commands-only provider)', function (): void {
            $provider = new ValueObjectsServiceProvider(app());
            expect($provider->provides())->toBeEmpty();
        });
    });

    describe('Console Commands', function (): void {
        it('both commands are final', function (): void {
            expect((new ReflectionClass(ListValueObjectsCommand::class))->isFinal())->toBeTrue();
            expect((new ReflectionClass(MakeValueObjectCommand::class))->isFinal())->toBeTrue();
        });
    });

    describe('Constructor Void Return Types', function (): void {
        $classes = [
            ValueObjectsException::class,
            InvalidValueObjectsArgumentException::class,
            ValueObjectsRuntimeException::class,
            Address::class,
            Coordinates::class,
            Currency::class,
            Duration::class,
            Email::class,
            Money::class,
            Percentage::class,
            PhoneNumber::class,
            Url::class,
            ValueObjectCast::class,
            ExchangeRateProvider::class,
        ];

        it('has :void on all constructors', function () use ($classes): void {
            foreach ($classes as $class) {
                $ref = new ReflectionClass($class);
                $ctor = $ref->getConstructor();
                if ($ctor !== null) {
                    expect($ctor->getReturnType()?->getName())->toBe('void', $class . ' constructor must return void');
                }
            }
        });
    });

    describe('Composer Metadata', function (): void {
        it('requires PHP ^8.5', function (): void {
            $composer = json_decode(file_get_contents(__DIR__ . '/../composer.json'), true);
            expect($composer['require']['php'])->toBe('^8.5');
        });

        it('has correct namespace', function (): void {
            $composer = json_decode(file_get_contents(__DIR__ . '/../composer.json'), true);
            expect($composer['autoload']['psr-4']['ZeroBoiler\\ValueObjects\\'])->toBe('src/');
        });

        it('registers provider', function (): void {
            $composer = json_decode(file_get_contents(__DIR__ . '/../composer.json'), true);
            expect($composer['extra']['laravel']['providers'])->toContain(
                'ZeroBoiler\\ValueObjects\\ValueObjectsServiceProvider'
            );
        });

        it('has quality scripts', function (): void {
            $composer = json_decode(file_get_contents(__DIR__ . '/../composer.json'), true);
            expect($composer['scripts'])->toHaveKey('test');
            expect($composer['scripts'])->toHaveKey('analyse');
            expect($composer['scripts'])->toHaveKey('lint');
            expect($composer['scripts'])->toHaveKey('rector');
            expect($composer['scripts'])->toHaveKey('quality');
        });

        it('has MIT license', function (): void {
            $composer = json_decode(file_get_contents(__DIR__ . '/../composer.json'), true);
            expect($composer['license'])->toBe('MIT');
        });
    });

    describe('Version Consistency', function (): void {
        it('composer.json has version 1.46.0', function (): void {
            $composer = json_decode(file_get_contents(__DIR__ . '/../composer.json'), true);
            expect($composer['version'])->toBe('1.46.0');
        });

        it('README shows version badge', function (): void {
            $readme = file_get_contents(__DIR__ . '/../README.md');
            expect($readme)->toContain('version-1.46.0');
        });
    });

    describe('Project Structure Files', function (): void {
        $requiredFiles = [
            'README.md', 'CHANGELOG.md', 'CONTRIBUTING.md', 'LICENSE',
            'composer.json', 'phpstan.neon.dist', 'rector.php', 'pint.json', 'pest.xml',
        ];

        it('has all ' . count($requiredFiles) . ' required files', function () use ($requiredFiles): void {
            foreach ($requiredFiles as $file) {
                expect(file_exists(__DIR__ . '/../' . $file))->toBeTrue("Missing {$file}");
            }
        });
    });
});
