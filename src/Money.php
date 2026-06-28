<?php

/**
 * This file is part of ZeroBoiler, licensed under the proprietary license.
 */

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects;

use NumberFormatter;
use ValueError;

/**
 * Money value object using integer minor units for precision.
 *
 * Uses ISO 4217 currency codes and correct subunit divisors.
 *
 * @extends ValueObject<array<string, mixed>>
 */
final class Money extends ValueObject
{
    use Castable;

    /** Amount in minor units (cents, fils, etc.) */
    public readonly int $amount;

    /** ISO 4217 currency code (e.g., "USD", "EUR") */
    public readonly string $currency;

    /**
     * Currencies with zero decimal places (no "cents" subunit).
     * Source: ISO 4217.
     *
     * @var array<int, string>
     */
    private const array ZERO_DECIMAL_CURRENCIES = [
        'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA',
        'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF',
    ];

    /**
     * Currencies with 3 decimal places.
     *
     * @var array<int, string>
     */
    private const array THREE_DECIMAL_CURRENCIES = [
        'BHD', 'IQD', 'JOD', 'KWD', 'LYD', 'OMR', 'TND',
    ];

    /**
     * @param  int  $amount  Amount in minor units (cents)
     * @param  string  $currency  ISO 4217 currency code
     */
    public function __construct(int $amount, string $currency = 'USD')
    {
        $this->validate(
            ['amount' => $amount, 'currency' => $currency],
            [
                'amount' => 'required|integer',
                'currency' => 'required|string|size:3',
            ]
        );

        $this->amount = $amount;
        $this->currency = strtoupper($currency);
    }

    /**
     * Add two money values (must be same currency).
     *
     * @throws ValueError If currencies differ
     */
    public function add(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->amount + $other->amount, $this->currency);
    }

    /**
     * Subtract money from this value.
     *
     * @throws ValueError If currencies differ
     */
    public function subtract(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->amount - $other->amount, $this->currency);
    }

    /**
     * Multiply by a factor.
     *
     * Uses BCMath when available for precise integer arithmetic.
     * Falls back to float with rounding for environments without ext-bcmath.
     * Both paths use round-half-away-from-zero for consistency.
     */
    public function multiply(float $factor): self
    {
        if (extension_loaded('bcmath')) {
            // Scale=1 to preserve a decimal for rounding, then round consistently
            $product = bcmul((string) $this->amount, (string) $factor, 1);
            // Apply round-half-away-from-zero to match non-bcmath behavior
            $newAmount = (int) ($product >= 0 ? bcadd($product, '0.5', 1) : bcsub($product, '0.5', 1));
        } else {
            $newAmount = (int) round($this->amount * $factor);
        }

        return new self($newAmount, $this->currency);
    }

    /**
     * Divide by a divisor.
     *
     * Uses BCMath when available for precise integer arithmetic.
     * Both paths use round-half-away-from-zero for consistency.
     *
     * @throws ValueError If divisor is zero
     */
    public function divide(float $divisor): self
    {
        if ($divisor === 0.0) {
            throw new ValueError('Cannot divide money by zero');
        }

        if (extension_loaded('bcmath')) {
            // Scale=1 for rounding precision, then round consistently
            $quotient = bcdiv((string) $this->amount, (string) $divisor, 1);
            $newAmount = (int) ($quotient >= 0 ? bcadd($quotient, '0.5', 1) : bcsub($quotient, '0.5', 1));
        } else {
            $newAmount = (int) round($this->amount / $divisor);
        }

        return new self($newAmount, $this->currency);
    }

    /**
     * Apply a percentage (e.g., tax) and return the result.
     *
     * Example: Money::fromMajor(9.99, 'EUR')->percentage(19) for 19% VAT.
     *
     * @param  float  $percent  Percentage to apply (e.g., 19 for 19%)
     */
    public function percentage(float $percent): self
    {
        return $this->multiply($percent / 100);
    }

    /**
     * Check if amount is zero.
     */
    public function isZero(): bool
    {
        return $this->amount === 0;
    }

    /**
     * Check if amount is positive.
     */
    public function isPositive(): bool
    {
        return $this->amount > 0;
    }

    /**
     * Check if amount is negative.
     */
    public function isNegative(): bool
    {
        return $this->amount < 0;
    }

    /**
     * Get the number of decimal places for this currency.
     *
     * Most currencies use 2 (e.g., USD: 100 cents = 1 dollar).
     * JPY, KRW, etc. use 0 (no subunit).
     * BHD, KWD, etc. use 3.
     */
    public function decimalPlaces(): int
    {
        if (in_array($this->currency, self::ZERO_DECIMAL_CURRENCIES, true)) {
            return 0;
        }

        if (in_array($this->currency, self::THREE_DECIMAL_CURRENCIES, true)) {
            return 3;
        }

        return 2;
    }

    /**
     * Get the subunit divisor (e.g., 100 for USD, 1 for JPY, 1000 for KWD).
     */
    public function subunitDivisor(): int
    {
        return 10 ** $this->decimalPlaces();
    }

    /**
     * Format money for display.
     *
     * Uses NumberFormatter with correct subunit divisor per ISO 4217.
     *
     * @param  string|null  $locale  Locale for formatting (e.g., "en_US")
     * @return string Formatted amount (e.g., "$1,234.56" or "¥1,234")
     */
    public function format(?string $locale = null): string
    {
        $locale ??= 'en_US';
        $decimals = $this->decimalPlaces();

        $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
        $formatter->setTextAttribute(NumberFormatter::CURRENCY_CODE, $this->currency);
        $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, $decimals);

        // Use bcmath for precision-safe division to avoid float overflow
        // for large amounts (e.g., billions in minor units).
        if (extension_loaded('bcmath')) {
            $major = bcdiv(
                (string) $this->amount,
                (string) $this->subunitDivisor(),
                $decimals,
            );
        } else {
            // Fallback: use float but with explicit precision control
            $major = number_format(
                $this->amount / $this->subunitDivisor(),
                $decimals,
                '.',
                '',
            );
        }

        return $formatter->format((float) $major);
    }

    /**
     * Get amount in major units (dollars, euros, etc.).
     */
    public function toMajor(): float
    {
        // Use bcmath for precision-safe conversion when available
        if (extension_loaded('bcmath')) {
            return (float) bcdiv(
                (string) $this->amount,
                (string) $this->subunitDivisor(),
                $this->decimalPlaces(),
            );
        }

        return $this->amount / $this->subunitDivisor();
    }

    /**
     * Create from major units (dollars, euros, etc.).
     *
     * @param  float  $amount  Amount in major units
     */
    public static function fromMajor(float $amount, string $currency = 'USD'): self
    {
        $temp = new self(0, $currency);
        $divisor = $temp->subunitDivisor();

        return new self((int) round($amount * $divisor), $currency);
    }

    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'currency' => $this->currency,
        ];
    }

    public function __toString(): string
    {
        return $this->format();
    }

    /**
     * Ensure currencies match for arithmetic operations.
     *
     * @throws ValueError If currencies differ
     */
    private function assertSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new ValueError(
                "Cannot operate on Money with different currencies: {$this->currency} vs {$other->currency}"
            );
        }
    }
}
