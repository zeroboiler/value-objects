<?php

/**
 * This file is part of ZeroBoiler, licensed under the proprietary license.
 */

declare(strict_types=1);

use ZeroBoiler\ValueObjects\Currency;
use ZeroBoiler\ValueObjects\ExchangeRateProvider;
use ZeroBoiler\ValueObjects\Money;

// --- Interface contract ---

test('exchange rate provider is an interface', function (): void {
    $reflection = new ReflectionClass(ExchangeRateProvider::class);

    expect($reflection->isInterface())->toBeTrue();
});

test('exchange rate provider has getRate method', function (): void {
    $reflection = new ReflectionClass(ExchangeRateProvider::class);

    expect($reflection->hasMethod('getRate'))->toBeTrue();
});

test('exchange rate provider getRate returns float', function (): void {
    $method = ExchangeRateProvider::class;

    $reflection = new ReflectionClass($method);
    $getRate = $reflection->getMethod('getRate');
    $returnType = $getRate->getReturnType();

    expect($returnType)->not->toBeNull()
        ->and($returnType->getName())->toBe('float');
});

// --- Real implementation usage ---

test('exchange rate provider can be implemented and used with convertVia', function (): void {
    $provider = new class implements ExchangeRateProvider
    {
        public function getRate(string $from, string $to): float
        {
            $rates = [
                'USD' => ['EUR' => 0.92, 'GBP' => 0.79, 'JPY' => 150.0],
                'EUR' => ['USD' => 1.09, 'GBP' => 0.86, 'JPY' => 163.0],
                'GBP' => ['USD' => 1.27, 'EUR' => 1.16, 'JPY' => 190.0],
                'JPY' => ['USD' => 0.0067, 'EUR' => 0.0061, 'GBP' => 0.0053],
            ];

            $rate = $rates[$from][$to] ?? null;

            if ($rate === null || $rate <= 0) {
                throw new ValueError("No rate available for {$from} to {$to}");
            }

            return $rate;
        }
    };

    $usd = Money::usd(10000); // $100.00
    $eur = $usd->convertVia(Currency::fromCode('EUR'), $provider);

    expect($eur->amount)->toBe(9200) // 100 * 0.92 = 92.00 = 9200 cents
        ->and($eur->currency()->code)->toBe('EUR');

    $gbp = $usd->convertVia('GBP', $provider);

    expect($gbp->amount)->toBe(7900)
        ->and($gbp->currency()->code)->toBe('GBP');

    $jpy = $usd->convertVia('JPY', $provider);

    expect($jpy->amount)->toBe(15000) // 100 * 150 = 15000 JPY (0 decimals)
        ->and($jpy->currency()->code)->toBe('JPY');
});

test('exchange rate provider can be used for reverse conversion', function (): void {
    $provider = new class implements ExchangeRateProvider
    {
        public function getRate(string $from, string $to): float
        {
            return match (true) {
                $from === 'EUR' && $to === 'USD' => 1.09,
                $from === 'USD' && $to === 'EUR' => 0.92,
                default => throw new ValueError("Unknown rate {$from}→{$to}"),
            };
        }
    };

    $eur = Money::eur(10000); // €100.00
    $usd = $eur->convertVia('USD', $provider);

    expect($usd->amount)->toBe(10900)
        ->and($usd->currency()->code)->toBe('USD');
});

test('exchange rate provider handles same currency with rate 1', function (): void {
    $provider = new class implements ExchangeRateProvider
    {
        public function getRate(string $from, string $to): float
        {
            if ($from === $to) {
                return 1.0;
            }

            throw new ValueError('No cross rates available');
        }
    };

    $usd = Money::usd(5000);
    $result = $usd->convertVia('USD', $provider);

    expect($result->amount)->toBe(5000)
        ->and($result->currency()->code)->toBe('USD');
});

test('exchange rate provider implementation throwing ValueError propagates', function (): void {
    $provider = new class implements ExchangeRateProvider
    {
        public function getRate(string $from, string $to): float
        {
            throw new ValueError("No rate for {$from}→{$to}");
        }
    };

    $usd = Money::usd(1000);

    expect(fn (): Money => $usd->convertVia('EUR', $provider))
        ->toThrow(ValueError::class);
});

// --- Provider with caching pattern ---

test('exchange rate provider can implement caching pattern', function (): void {
    $callCount = 0;

    $provider = new class($callCount) implements ExchangeRateProvider
    {
        /** @param-out int $callCount */
        private array $cache = [];

        public function __construct(
            /** @noinspection PhpParameterOnlyTypedIsNotUsedInspection */
            private int &$callCount
        ) {}

        public function getRate(string $from, string $to): float
        {
            $key = "{$from}{$to}";

            if (isset($this->cache[$key])) {
                return $this->cache[$key];
            }

            $this->callCount++;

            return $this->cache[$key] = 0.92; // Static rate for testing
        }
    };

    $usd = Money::usd(10000);

    // First call should hit the provider
    $eur1 = $usd->convertVia('EUR', $provider);
    expect($eur1->amount)->toBe(9200);

    // Second conversion should use cache
    $eur2 = $usd->convertVia('EUR', $provider);
    expect($eur2->amount)->toBe(9200);

    expect($callCount)->toBe(1); // Only one actual rate lookup
});
