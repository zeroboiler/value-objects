<?php
/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

use ReflectionClass;
use ReflectionMethod;
use ZeroBoiler\ValueObjects\Address;
use ZeroBoiler\ValueObjects\CastableAs;
use ZeroBoiler\ValueObjects\Contracts\ValueObject;
use ZeroBoiler\ValueObjects\Coordinates;
use ZeroBoiler\ValueObjects\Currency;
use ZeroBoiler\ValueObjects\Duration;
use ZeroBoiler\ValueObjects\Email;
use ZeroBoiler\ValueObjects\Exceptions\ValueObjectsException;
use ZeroBoiler\ValueObjects\ExchangeRateProvider;
use ZeroBoiler\ValueObjects\Money;
use ZeroBoiler\ValueObjects\Percentage;
use ZeroBoiler\ValueObjects\PhoneNumber;
use ZeroBoiler\ValueObjects\Url;
use ZeroBoiler\ValueObjects\ValueObjectCast;
use ZeroBoiler\ValueObjects\ValueObjectInterface;

// ═══════════════════════════════════════════════════════════════════════════════
// Phase 34 — Full Production Readiness Audit
// ═══════════════════════════════════════════════════════════════════════════════

describe('Phase 34 Production Audit', function (): void {

    test('composer.json version is 1.40.0', function (): void {
        $json = json_decode(file_get_contents(__DIR__.'/../composer.json'), true);
        expect($json['version'])->toBe('1.40.0');
    });

    test('all 22 source files declare strict_types=1', function (): void {
        $srcFiles = glob(__DIR__.'/../src/**/*.php');
        expect(is_array($srcFiles) ? count($srcFiles) : 0)->toBe(22);
        $violations = [];
        foreach ($srcFiles as $file) {
            if (! str_contains(file_get_contents($file), 'declare(strict_types=1)')) {
                $violations[] = basename($file);
            }
        }
        expect($violations)->toBeEmpty();
    });

    test('all source files have MIT license header', function (): void {
        $violations = [];
        foreach (glob(__DIR__.'/../src/**/*.php') as $file) {
            if (! str_contains(file_get_contents($file), 'This file is part of ZeroBoiler, licensed under the MIT license.')) {
                $violations[] = basename($file);
            }
        }
        expect($violations)->toBeEmpty();
    });

    test('no TODO/FIXME/HACK markers', function (): void {
        $violations = [];
        foreach (glob(__DIR__.'/../src/**/*.php') as $file) {
            if (preg_match('/\b(TODO|FIXME|HACK|XXX)\b/', file_get_contents($file), $m)) {
                $violations[] = basename($file).':'.$m[0];
            }
        }
        expect($violations)->toBeEmpty();
    });

    test('test file count is 31 (including this test)', function (): void {
        $testFiles = glob(__DIR__.'/../tests/**/*.php');
        expect(is_array($testFiles) ? count($testFiles) : 0)->toBe(31);
    });

    test('all value object constructors have :void', function (): void {
        $vos = [
            Url::class, Money::class, Currency::class, PhoneNumber::class, Email::class,
            Percentage::class, Duration::class, Coordinates::class, Address::class,
        ];
        $violations = [];
        foreach ($vos as $vo) {
            $ref = new ReflectionClass($vo);
            $ctor = $ref->getConstructor();
            if ($ctor !== null) {
                $rt = $ctor->getReturnType();
                if ($rt === null || $rt->getName() !== 'void') {
                    $violations[] = $ref->getShortName();
                }
            }
        }
        expect($violations)->toBeEmpty();
    });

    test('all value objects implement ValueObject interface', function (): void {
        $vos = [
            Url::class, Money::class, Currency::class, PhoneNumber::class, Email::class,
            Percentage::class, Duration::class, Coordinates::class, Address::class,
        ];
        foreach ($vos as $vo) {
            expect((new ReflectionClass($vo))->implementsInterface(ValueObject::class))->toBeTrue("{$vo} should implement ValueObject");
        }
    });

    test('ValueObjectInterface is @deprecated pointing to ValueObject', function (): void {
        $ref = new ReflectionClass(ValueObjectInterface::class);
        $doc = $ref->getDocComment();
        expect($doc)->not()->toBeNull();
        expect(str_contains($doc, '@deprecated'))->toBeTrue();
        expect(str_contains($doc, 'ValueObject'))->toBeTrue();
    });

    test('ValueObjectsException is abstract base', function (): void {
        $ref = new ReflectionClass(ValueObjectsException::class);
        expect($ref->isAbstract())->toBeTrue();
    });

    test('ServiceProvider has #[Override] on register and boot', function (): void {
        $ref = new ReflectionClass(\ZeroBoiler\ValueObjects\ValueObjectsServiceProvider::class);
        expect($ref->getMethod('register')->hasAttribute(\Override::class))->toBeTrue();
        expect($ref->getMethod('boot')->hasAttribute(\Override::class))->toBeTrue();
    });

    test('all value objects are final', function (): void {
        $vos = [
            Url::class, Money::class, Currency::class, PhoneNumber::class, Email::class,
            Percentage::class, Duration::class, Coordinates::class, Address::class,
        ];
        foreach ($vos as $vo) {
            expect((new ReflectionClass($vo))->isFinal())->toBeTrue("{$vo} should be final");
        }
    });

    test('all value objects implement JsonSerializable', function (): void {
        $vos = [
            Url::class, Money::class, Currency::class, PhoneNumber::class, Email::class,
            Percentage::class, Duration::class, Coordinates::class, Address::class,
        ];
        foreach ($vos as $vo) {
            expect((new ReflectionClass($vo))->implementsInterface(\JsonSerializable::class))->toBeTrue("{$vo} should implement JsonSerializable");
        }
    });

    test('public methods have return types', function (): void {
        $vos = [Url::class, Money::class, Email::class, Currency::class, PhoneNumber::class];
        $violations = [];
        foreach ($vos as $vo) {
            $ref = new ReflectionClass($vo);
            foreach ($ref->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if (str_starts_with($method->getName(), '__')) continue;
                if ($method->getReturnType() === null) {
                    $violations[] = $ref->getShortName().'::'.$method->getName();
                }
            }
        }
        expect($violations)->toBeEmpty();
    });
});
