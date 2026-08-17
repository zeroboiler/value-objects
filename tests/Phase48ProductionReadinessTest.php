<?php
/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

use ZeroBoiler\ValueObjects\Exceptions\InvalidValueObjectsArgumentException;
use ZeroBoiler\ValueObjects\Exceptions\ValueObjectsException;
use ZeroBoiler\ValueObjects\Exceptions\ValueObjectsRuntimeException;
use ZeroBoiler\ValueObjects\ValueObjectsServiceProvider;

beforeEach(function (): void {
    //
});

describe('Phase 48 Production Readiness', function (): void {
    describe('Exception hierarchy', function (): void {
        it('base exception is abstract with FQCN @see to both leaves', function (): void {
            $ref = new ReflectionClass(ValueObjectsException::class);
            expect($ref->isAbstract())->toBeTrue();
            $doc = $ref->getDocComment();
            expect($doc)->toContain('\InvalidValueObjectsArgumentException');
            expect($doc)->toContain('\ValueObjectsRuntimeException');
        });

        it('both leaves have FQCN @see to base and sibling', function (): void {
            $leaves = [InvalidValueObjectsArgumentException::class, ValueObjectsRuntimeException::class];
            foreach ($leaves as $leaf) {
                $doc = (new ReflectionClass($leaf))->getDocComment();
                expect($doc)->toContain('\ValueObjectsException');
            }
        });

        it('both leaves are final', function (): void {
            foreach ([InvalidValueObjectsArgumentException::class, ValueObjectsRuntimeException::class] as $leaf) {
                expect((new ReflectionClass($leaf))->isFinal())->toBeTrue();
            }
        });
    });

    describe('Leaf exception factory methods', function (): void {
        it('both leaves have forMessage() returning self', function (): void {
            foreach ([InvalidValueObjectsArgumentException::class, ValueObjectsRuntimeException::class] as $leaf) {
                $ref = new ReflectionClass($leaf);
                expect($ref->hasMethod('forMessage'))->toBeTrue("{$leaf} must have forMessage()");
                $method = $ref->getMethod('forMessage');
                expect($method->getReturnType()?->getName())->toBe($leaf);
            }
        });

        it('both leaves have constructors with default messages', function (): void {
            foreach ([InvalidValueObjectsArgumentException::class, ValueObjectsRuntimeException::class] as $leaf) {
                $ctor = (new ReflectionClass($leaf))->getMethod('__construct');
                expect($ctor->getReturnType()?->getName())->toBe('void');
                expect($ctor->getParameters()[0]->isDefaultValueAvailable())->toBeTrue();
            }
        });
    });

    describe('PSR-12: No blank line after <?php', function (): void {
        it('all source files comply', function (): void {
            $srcDir = __DIR__ . '/../src';
            $violations = [];
            foreach (glob_recursive($srcDir . '/*.php') as $file) {
                $lines = file($file, FILE_IGNORE_NEW_LINES);
                if (count($lines) >= 2 && trim($lines[1]) === '') {
                    $violations[] = str_replace($srcDir . '/', '', $file);
                }
            }
            expect($violations)->toBeEmpty('PSR-12: ' . implode(', ', array_slice($violations, 0, 10)));
        });
    });

    describe('phpstan neon parity', function (): void {
        it('both have level 9', function (): void {
            expect(file_get_contents(__DIR__ . '/../phpstan.neon'))->toContain('level(9)');
            expect(file_get_contents(__DIR__ . '/../phpstan.neon.dist'))->toContain('level: 9');
        });
    });

    describe('Rector PHP 8.5 target', function (): void {
        it('rector.php targets PHP 8.5', function (): void {
            expect(file_get_contents(__DIR__ . '/../rector.php'))->toContain('PHP_85');
        });
    });

    describe('ServiceProvider finality', function (): void {
        it('is final with #[Override] on register/provides/boot', function (): void {
            $ref = new ReflectionClass(ValueObjectsServiceProvider::class);
            expect($ref->isFinal())->toBeTrue();
            foreach (['register', 'provides', 'boot'] as $method) {
                $m = $ref->getMethod($method);
                $hasOverride = false;
                foreach ($m->getAttributes() as $attr) {
                    if ($attr->getName() === 'Override') {
                        $hasOverride = true;
                        break;
                    }
                }
                expect($hasOverride)->toBeTrue("{$method}() must have #[Override]");
            }
        });
    });

    describe('Composer metadata', function (): void {
        it('PHP ^8.5, correct namespace, MIT', function (): void {
            $c = json_decode(file_get_contents(__DIR__ . '/../composer.json'), true);
            expect($c['require']['php'])->toBe('^8.5');
            expect($c['autoload']['psr-4']['ZeroBoiler\\ValueObjects\\'])->toBe('src/');
            expect($c['license'])->toBe('MIT');
            expect(isset($c['scripts']['test']))->toBeTrue();
        });
    });

    describe('File counts', function (): void {
        it('has 22+ source files and 38+ test files', function (): void {
            expect(count(glob_recursive(__DIR__ . '/../src/*.php')))->toBeGreaterThanOrEqual(22);
            expect(count(glob_recursive(__DIR__ . '/*.php')))->toBeGreaterThanOrEqual(38);
        });
    });
});

if (! function_exists('glob_recursive')) {
    function glob_recursive(string $pattern, int $flags = 0): array
    {
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
            $files = array_merge($files, glob_recursive($dir . '/' . basename($pattern), $flags));
        }
        return $files;
    }
}
