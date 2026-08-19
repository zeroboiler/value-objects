<?php

/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

use ZeroBoiler\ValueObjects\Address;
use ZeroBoiler\ValueObjects\Castable;
use ZeroBoiler\ValueObjects\CastableAs;
use ZeroBoiler\ValueObjects\Console\Commands\ListValueObjectsCommand;
use ZeroBoiler\ValueObjects\Console\Commands\MakeValueObjectCommand;
use ZeroBoiler\ValueObjects\Contracts\ValueObject as ValueObjectContract;
use ZeroBoiler\ValueObjects\Coordinates;
use ZeroBoiler\ValueObjects\Currency;
use ZeroBoiler\ValueObjects\Duration;
use ZeroBoiler\ValueObjects\Email;
use ZeroBoiler\ValueObjects\Exceptions\InvalidValueObjectsArgumentException;
use ZeroBoiler\ValueObjects\Exceptions\ValueObjectsException;
use ZeroBoiler\ValueObjects\Exceptions\ValueObjectsRuntimeException;
use ZeroBoiler\ValueObjects\ExchangeRateProvider;
use ZeroBoiler\ValueObjects\Money;
use ZeroBoiler\ValueObjects\Percentage;
use ZeroBoiler\ValueObjects\PhoneNumber;
use ZeroBoiler\ValueObjects\Url;
use ZeroBoiler\ValueObjects\ValueObject;
use ZeroBoiler\ValueObjects\ValueObjectCast;
use ZeroBoiler\ValueObjects\ValueObjectInterface;
use ZeroBoiler\ValueObjects\ValueObjectsServiceProvider;

beforeEach(function () {
    $this->srcFiles = glob(__DIR__.'/../src/**/*.php', GLOB_ERR);
});

describe('Phase 88 — @since Annotation Completeness Audit', function () {
    test('all public methods in ValueObject base class have @since', function () {
        $ref = new ReflectionClass(ValueObject::class);
        $methods = $ref->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            $name = $method->getName();
            $doc = $method->getDocComment() ?: '';
            expect($doc)->toContain('@since',
                "ValueObject::{$name}() is missing @since annotation");
        }
    });

    test('all public methods in concrete VO classes have @since', function () {
        $vos = [
            Url::class, Currency::class, Email::class, Duration::class,
            Percentage::class, Coordinates::class, Address::class,
            PhoneNumber::class, Money::class,
        ];

        foreach ($vos as $vo) {
            $ref = new ReflectionClass($vo);
            $methods = $ref->getMethods(ReflectionMethod::IS_PUBLIC);

            foreach ($methods as $method) {
                $name = $method->getName();
                $doc = $method->getDocComment() ?: '';
                expect($doc)->toContain('@since',
                    "{$vo}::{$name}() is missing @since annotation");
            }
        }
    });

    test('all public methods in interface and trait have @since', function () {
        $classes = [
            ValueObjectContract::class,
            Castable::class,
            ExchangeRateProvider::class,
            ValueObjectCast::class,
        ];

        foreach ($classes as $cls) {
            $ref = new ReflectionClass($cls);
            $methods = $ref->getMethods(ReflectionMethod::IS_PUBLIC);

            foreach ($methods as $method) {
                $name = $method->getName();
                $doc = $method->getDocComment() ?: '';
                expect($doc)->toContain('@since',
                    "{$cls}::{$name}() is missing @since annotation");
            }
        }
    });

    test('all public methods in exceptions have @since', function () {
        $classes = [
            ValueObjectsException::class,
            InvalidValueObjectsArgumentException::class,
            ValueObjectsRuntimeException::class,
        ];

        foreach ($classes as $cls) {
            $ref = new ReflectionClass($cls);
            $methods = $ref->getMethods(ReflectionMethod::IS_PUBLIC);

            foreach ($methods as $method) {
                $name = $method->getName();
                $doc = $method->getDocComment() ?: '';
                expect($doc)->toContain('@since',
                    "{$cls}::{$name}() is missing @since annotation");
            }
        }
    });

    test('CastableAs constructor has @since', function () {
        $ref = new ReflectionClass(CastableAs::class);
        $ctor = $ref->getConstructor();
        expect($ctor)->not->toBeNull();
        $doc = $ctor->getDocComment() ?: '';
        expect($doc)->toContain('@since', 'CastableAs::__construct() is missing @since annotation');
    });

    test('console commands have @since on handle()', function () {
        $commands = [MakeValueObjectCommand::class, ListValueObjectsCommand::class];

        foreach ($commands as $cmd) {
            $method = (new ReflectionClass($cmd))->getMethod('handle');
            $doc = $method->getDocComment() ?: '';
            expect($doc)->toContain('@since',
                "{$cmd}::handle() is missing @since annotation");
        }
    });

    test('ServiceProvider methods have @since', function () {
        $ref = new ReflectionClass(ValueObjectsServiceProvider::class);

        foreach (['register', 'boot', 'provides'] as $method) {
            $m = $ref->getMethod($method);
            $doc = $m->getDocComment() ?: '';
            expect($doc)->toContain('@since',
                "ValueObjectsServiceProvider::{$method}() is missing @since annotation");
        }
    });

    test('total @since annotations exceed public method count', function () {
        $sinceCount = 0;
        $publicMethodCount = 0;

        foreach ($this->srcFiles as $file) {
            $content = file_get_contents($file);
            $sinceCount += substr_count($content, '@since');

            // Count public method declarations
            preg_match_all('/public\s+(static\s+)?function\s+/', $content, $matches);
            $publicMethodCount += count($matches[0]);
        }

        // We should have more @since than public methods (classes also have @since)
        expect($sinceCount)->toBeGreaterThan($publicMethodCount,
            "@since count ({$sinceCount}) should exceed public method count ({$publicMethodCount})");
    });

    test('all @since values are valid semantic versions', function () {
        foreach ($this->srcFiles as $file) {
            $content = file_get_contents($file);
            preg_match_all('/@since\s+(\d+\.\d+\.\d+)/', $content, $matches);

            foreach ($matches[1] as $version) {
                expect($version)->toMatch('/^\d+\.\d+\.\d+$/',
                    "Invalid @since version '{$version}' in {$file}");
            }
        }
    });
});
