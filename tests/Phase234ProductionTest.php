<?php

/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects\Tests;

use ReflectionClass;

test('Phase 2: strict types on all source files', function (): void {
    $files = glob(__DIR__.'/../src/**/*.php');
    expect($files)->not->toBeEmpty();
    foreach ($files as $file) {
        expect(file_get_contents($file), "strict_types missing in {$file}")->toContain('declare(strict_types=1)');
    }
});

test('Phase 2: no TODO/FIXME markers', function (): void {
    $files = glob(__DIR__.'/../src/**/*.php');
    foreach ($files as $file) {
        $c = file_get_contents($file);
        expect($c)->not->toContain('TODO');
        expect($c)->not->toContain('FIXME');
    }
});

test('Phase 2: composer.json PHP 8.5+ and stable', function (): void {
    $c = json_decode(file_get_contents(__DIR__.'/../composer.json'), true, 512, JSON_THROW_ON_ERROR);
    expect($c['require']['php'])->toBe('^8.5');
    expect($c['minimum-stability'])->toBe('stable');
    expect($c['prefer-stable'])->toBeTrue();
});

test('Phase 3: ServiceProvider final with #[Override]', function (): void {
    $r = new ReflectionClass(\ZeroBoiler\ValueObjects\ValueObjectsServiceProvider::class);
    expect($r->isFinal())->toBeTrue();
    foreach (['register', 'boot', 'provides'] as $m) {
        $method = $r->getMethod($m);
        $has = array_any($method->getAttributes(), fn (\ReflectionAttribute $a): bool => $a->getName() === 'Override');
        expect($has, "ServiceProvider::{$m}() needs #[Override]")->toBeTrue();
    }
});

test('Phase 4: all public classes have @since annotation', function (): void {
    $classes = [
        \ZeroBoiler\ValueObjects\ValueObject::class,
        \ZeroBoiler\ValueObjects\ValueObjectInterface::class,
        \ZeroBoiler\ValueObjects\Contracts\ValueObject::class,
        \ZeroBoiler\ValueObjects\ValueObjectsServiceProvider::class,
        \ZeroBoiler\ValueObjects\Email::class,
        \ZeroBoiler\ValueObjects\Url::class,
        \ZeroBoiler\ValueObjects\PhoneNumber::class,
        \ZeroBoiler\ValueObjects\Money::class,
        \ZeroBoiler\ValueObjects\Currency::class,
        \ZeroBoiler\ValueObjects\Address::class,
        \ZeroBoiler\ValueObjects\Coordinates::class,
        \ZeroBoiler\ValueObjects\Duration::class,
        \ZeroBoiler\ValueObjects\Percentage::class,
        \ZeroBoiler\ValueObjects\ExchangeRateProvider::class,
        \ZeroBoiler\ValueObjects\Castable::class,
        \ZeroBoiler\ValueObjects\CastableAs::class,
        \ZeroBoiler\ValueObjects\ValueObjectCast::class,
    ];

    foreach ($classes as $class) {
        $ref = new ReflectionClass($class);
        $doc = $ref->getDocComment();
        expect($doc)->not->toBeFalse("{$class} has no docblock");
        expect($doc)->toContain('@since', "{$class} is missing @since annotation in docblock");
    }
});

test('Phase 4: all value object classes are final or readonly', function (): void {
    $files = glob(__DIR__.'/../src/**/*.php');
    foreach ($files as $file) {
        $content = file_get_contents($file);
        if (!preg_match('/\bclass\s+(\w+)/', $content, $cm)) {
            continue;
        }
        $className = $cm[1];
        if (str_contains($content, 'interface ') || str_contains($content, 'abstract class ') || str_contains($content, 'trait ')) {
            continue;
        }
        $isFinalOrReadonly = str_contains($content, 'readonly class ') || str_contains($content, "final class {$className}");
        expect($isFinalOrReadonly, "{$className} in {$file} should be final or readonly")->toBeTrue();
    }
});

test('Phase 4: version consistency', function (): void {
    $c = json_decode(file_get_contents(__DIR__.'/../composer.json'), true, 512, JSON_THROW_ON_ERROR);
    expect($c['version'])->toMatch('/^\d+\.\d+\.\d+$/');
});
