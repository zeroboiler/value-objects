<?php
/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

use Illuminate\Container\Container;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use ZeroBoiler\ValueObjects\Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Bootstrap Laravel Container for Validation
|--------------------------------------------------------------------------
|
| Set up a minimal Laravel container to support validation in tests.
|
*/

$container = Container::getInstance();

// Create a translator
$loader = new ArrayLoader;
$translator = new Translator($loader, 'en');

// Register validation factory
$container->instance(ValidationFactory::class, new Factory($translator));

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is actually bound
| to a PHPUnit TestCase subclass. This allows you to use all of
| PHPUnit's methods as you normally would.
|
*/

uses(TestCase::class)->in(__DIR__);

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectation" functions that let you
| assert on a variety of conditions.
|
*/

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/
