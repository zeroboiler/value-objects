<?php

/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects;

/**
 * Provider interface for currency exchange rate lookups.
 *
 * Implementations may fetch rates from external APIs (e.g., Fixer, ECB),
 * cache them, or use static configuration. This enables Money::convertVia()
 * to automatically resolve the correct exchange rate.
 *
 * Example implementation:
 *
 * ```php
 * class FixerExchangeRateProvider implements ExchangeRateProvider
 * {
 *     public function getRate(string $from, string $to): float
 *     {
 *         $response = Http::get("https://api.fixer.io/latest", [
 *             'base' => $from,
 *             'symbols' => $to,
 *         ]);
 *         return $response->json("rates.{$to}", 1.0);
 *     }
 * }
 * ```
 *
 * @since 1.0.0
 */
interface ExchangeRateProvider
{
    /**
     * Get the exchange rate from one currency to another.
     *
     * The rate represents: 1 unit of $from = X units of $to.
     * For example, if 1 USD = 0.92 EUR, getRate('USD', 'EUR') returns 0.92.
     *
     * @param  string  $from  Source ISO 4217 currency code
     * @param  string  $to  Target ISO 4217 currency code
     *
     * @throws \ValueError If the rate cannot be determined or is non-positive
     */
    public function getRate(string $from, string $to): float;
}
