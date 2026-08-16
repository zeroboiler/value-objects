<?php
/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

use ZeroBoiler\ValueObjects\Exceptions\ValueObjectsException;
use ZeroBoiler\ValueObjects\Exceptions\InvalidValueObjectsArgumentException;
use ZeroBoiler\ValueObjects\Exceptions\ValueObjectsRuntimeException;
use ZeroBoiler\ValueObjects\ValueobjectsServiceProvider;
use ZeroBoiler\ValueObjects\Facades\Valueobjects;

beforeEach(function (): void {
    //
});

describe('Phase 46 Production Readiness', function (): void {
    describe('PSR-12: No blank line after <?php opening tag', function (): void {
        it('all source files comply', function (): void {
            $files = glob_recursive(__DIR__ . '/../src/*.php');
            $violations = [];
            foreach ($files as $file) {
                $lines = file($file, FILE_IGNORE_NEW_LINES);
                if (count($lines) >= 2 && trim($lines[1]) === '') {
                    $violations[] = basename($file);
                }
            }
            expect($violations)->toBeEmpty('PSR-12 violation: ' . implode(', ', $violations));
        });

        it('all test files comply', function (): void {
            $files = glob_recursive(__DIR__ . '/*.php');
            $violations = [];
            foreach ($files as $file) {
                $lines = file($file, FILE_IGNORE_NEW_LINES);
                if (count($lines) >= 2 && trim($lines[1]) === '') {
                    $violations[] = basename($file);
                }
            }
            expect($violations)->toBeEmpty();
        });
    });

    describe('phpstan.neon parity', function (): void {
        it('phpstan.neon has all neon.dist settings', function (): void {
            $neon = file_get_contents(__DIR__ . '/../phpstan.neon');
            expect($neon)->toContain('level(9)');
            expect($neon)->toContain('treatPhpDocTypesAsCertain(false)');
            expect($neon)->toContain('reportUnmatchedIgnoredErrors(false)');
            expect($neon)->toContain('checkUnusedParameters(true)');
            expect($neon)->toContain('checkUninitializedProperties(true)');
        });
    });

    describe('Exception hierarchy bidirectional FQCN @see', function (): void {
        it('base has FQCN @see to all leaves', function (): void {
            $doc = (new ReflectionClass(ValueObjectsException::class))->getDocComment();
            expect($doc)->toContain('\\ZeroBoiler\ValueObjects\\Exceptions\\InvalidValueObjectsArgumentException');
            expect($doc)->toContain('\\ZeroBoiler\ValueObjects\\Exceptions\\ValueObjectsRuntimeException');

        });

        it('leaves are final with @see to base and siblings', function (): void {
            $leaves = [InvalidValueObjectsArgumentException::class, ValueObjectsRuntimeException::class];
            foreach ($leaves as $leaf) {
                $ref = new ReflectionClass($leaf);
                expect($ref->isFinal())->toBeTrue();
                $doc = $ref->getDocComment();
                expect($doc)->toContain('\\ZeroBoiler\ValueObjects\\Exceptions\\ValueObjectsException');
                expect($doc)->toContain('\\ZeroBoiler\ValueObjects\\Exceptions\\InvalidValueObjectsArgumentException');
                expect($doc)->toContain('\\ZeroBoiler\ValueObjects\\Exceptions\\ValueObjectsRuntimeException');

            }
        });

        it('base is abstract with :void constructor', function (): void {
            $ref = new ReflectionClass(ValueObjectsException::class);
            expect($ref->isAbstract())->toBeTrue();
            expect($ref->getMethod('__construct')->getReturnType()?->getName())->toBe('void');
        });
    });

    describe('ServiceProvider and Facade', function (): void {
        it('ServiceProvider is final', function (): void {
            expect((new ReflectionClass(ValueobjectsServiceProvider::class))->isFinal())->toBeTrue();
        });

        it('Facade is final', function (): void {
            expect((new ReflectionClass(Valueobjects::class))->isFinal())->toBeTrue();
        });
    });

    describe('Project structure files', function (): void {
        $required = ['phpstan.neon.dist', 'phpstan.neon', 'rector.php', 'composer.json', 'LICENSE', 'CHANGELOG.md', 'README.md', '.editorconfig', '.gitattributes'];
        foreach ($required as $file) {
            it("has {$file}", fn () => expect(file_exists(__DIR__ . '/../' . $file))->toBeTrue());
        }
    });

    describe('Composer metadata', function (): void {
        it('requires PHP ^8.5 and MIT license', function (): void {
            $c = json_decode(file_get_contents(__DIR__ . '/../composer.json'), true);
            expect($c['require']['php'])->toBe('^8.5');
            expect($c['license'])->toBe('MIT');
        });

        it('has quality dev tools', function (): void {
            $c = json_decode(file_get_contents(__DIR__ . '/../composer.json'), true);
            $d = array_keys($c['require-dev']);
            expect($d)->toContain('phpstan/phpstan');
            expect($d)->toContain('laravel/pint');
            expect($d)->toContain('rector/rector');
            expect($d)->toContain('pestphp/pest');
        });
    });

    describe('Source quality', function (): void {
        it('all files have strict_types and @since', function (): void {
            $files = glob_recursive(__DIR__ . '/../src/*.php');
            foreach ($files as $file) {
                $content = file_get_contents($file);
                expect($content)->toContain('declare(strict_types=1)');
                expect($content)->toContain('@since');
            }
        });

        it('no TODO/FIXME markers', function (): void {
            $files = glob_recursive(__DIR__ . '/../src/*.php');
            $bad = [];
            foreach ($files as $file) {
                if (preg_match('/\b(TODO|FIXME|HACK|XXX)\b/', file_get_contents($file))) {
                    $bad[] = basename($file);
                }
            }
            expect($bad)->toBeEmpty();
        });
    });

    describe('File counts', function (): void {
        it('has 22 source files', function (): void {
            expect(count(glob_recursive(__DIR__ . '/../src/*.php')))->toBe(22);
        });

        it('has 37+ test files', function (): void {
            expect(count(glob_recursive(__DIR__ . '/*.php')))->toBeGreaterThanOrEqual(37);
        });
    });
});


if (! function_exists('glob_recursive')) {
    function glob_recursive(string $pattern, int $flags = 0): array {
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
            $files = array_merge($files, glob_recursive($dir . '/' . basename($pattern), $flags));
        }
        return $files;
    }
}

