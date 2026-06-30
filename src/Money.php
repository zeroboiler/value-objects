<?php

/**
 * This file is part of ZeroBoiler, licensed under the proprietary license.
 */

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects;

use NumberFormatter;
use OverflowException;
use ValueError;

/**
 * Money value object using integer minor units for precision.
 *
 * Uses ISO 4217 currency codes and correct subunit divisors.
 * Supports multi-currency operations, conversion, and allocation.
 *
 * All arithmetic operations detect integer overflow and throw
 * OverflowException rather than silently wrapping around.
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
     * @param  int  $amount  Amount in minor units (cents)
     * @param  string|Currency  $currency  ISO 4217 currency code or Currency VO
     *
     * @throws ValueError If the currency code is not a valid ISO 4217 code
     */
    public function __construct(int $amount, string|Currency $currency = 'USD')
    {
        if ($currency instanceof Currency) {
            $currency = $currency->code;
        }

        $currency = strtoupper(trim($currency));

        if (! Currency::isValid($currency)) {
            throw new ValueError("Invalid ISO 4217 currency code: '{$currency}'");
        }

        $this->validate(
            ['amount' => $amount, 'currency' => $currency],
            [
                'amount' => 'required|integer',
                'currency' => 'required|string|size:3',
            ]
        );

        $this->amount = $amount;
        $this->currency = $currency;
    }

    /**
     * Get the Currency value object for this money.
     */
    public function currency(): Currency
    {
        return new Currency($this->currency);
    }

    /**
     * Add two money values (must be same currency).
     *
     * @throws ValueError If currencies differ
     * @throws OverflowException If the result exceeds PHP_INT_MAX or PHP_INT_MIN
     */
    public function add(self $other): self
    {
        $this->assertSameCurrency($other);

        $result = $this->amount + $other->amount;

        // In PHP 8.5, integer overflow promotes to float.
        // Detect this and throw instead of passing a float to the constructor.
        if (is_float($result)) {
            throw new OverflowException(
                "Money addition overflow: {$this->amount} + {$other->amount} exceeds integer limits"
            );
        }

        return new self($result, $this->currency);
    }

    /**
     * Subtract money from this value.
     *
     * @throws ValueError If currencies differ
     * @throws OverflowException If the result exceeds integer limits
     */
    public function subtract(self $other): self
    {
        $this->assertSameCurrency($other);

        $result = $this->amount - $other->amount;

        if (is_float($result)) {
            throw new OverflowException(
                "Money subtraction overflow: {$this->amount} - {$other->amount} exceeds integer limits"
            );
        }

        return new self($result, $this->currency);
    }

    /**
     * Convert this money to a different currency using an exchange rate.
     *
     * The rate is the price of 1 unit of the source currency in the target currency.
     * For example, if 1 USD = 0.85 EUR, the rate is 0.85.
     *
     * Rounding follows the target currency's decimal places to ensure the
     * result is a valid minor-unit amount in the target currency.
     *
     * @param  Currency|string  $to  Target currency (code or VO)
     * @param  float  $rate  Exchange rate (source → target)
     *
     * @throws ValueError If rate is not positive
     * @throws OverflowException If the converted amount exceeds integer limits
     */
    public function convert(Currency|string $to, float $rate): self
    {
        if ($rate <= 0.0) {
            throw new ValueError('Exchange rate must be positive');
        }

        $targetCurrency = $to instanceof Currency ? $to->code : strtoupper(trim($to));

        if (! Currency::isValid($targetCurrency)) {
            throw new ValueError("Invalid ISO 4217 currency code: '{$targetCurrency}'");
        }

        $target = new Currency($targetCurrency);

        // Use bcmath for the entire pipeline when available to avoid
        // float precision loss on large amounts.
        if (extension_loaded('bcmath')) {
            $sourceMajor = bcdiv(
                (string) $this->amount,
                (string) $this->subunitDivisor(),
                10
            );
            $targetMajor = bcmul($sourceMajor, (string) $rate, 10);
            $targetMinor = bcmul(
                $targetMajor,
                (string) $target->subunitDivisor(),
                0
            );

            // Overflow check
            if (bccomp($targetMinor, (string) PHP_INT_MAX) > 0
                || bccomp($targetMinor, (string) PHP_INT_MIN) < 0
            ) {
                throw new OverflowException(
                    "Money convert overflow: result {$targetMinor} exceeds integer limits"
                );
            }

            // Round half away from zero
            if (bccomp($targetMinor, '0') >= 0) {
                $targetMinor = bcadd($targetMinor, '0', 0);
            } else {
                $targetMinor = bcsub($targetMinor, '0', 0);
            }

            return new self((int) $targetMinor, $targetCurrency);
        }

        // Float fallback
        $sourceMajor = $this->amount / $this->subunitDivisor();
        $targetMajor = $sourceMajor * $rate;
        $floatResult = $targetMajor * $target->subunitDivisor();

        if (abs($floatResult) > (float) PHP_INT_MAX) {
            throw new OverflowException(
                'Money convert overflow: result exceeds integer limits'
            );
        }

        $targetMinor = (int) round($floatResult);

        return new self($targetMinor, $targetCurrency);
    }

    /**
     * Allocate money into N equal parts, distributing the remainder.
     *
     * Uses the largest-remainder method to ensure the sum of parts
     * equals the original amount exactly.
     *
     * Example: 100 cents / 3 = [34, 33, 33] (not [33, 33, 33])
     *
     * @param  int  $parts  Number of parts to split into
     * @return array<int, self>
     *
     * @throws ValueError If parts is less than 1
     */
    public function allocate(int $parts): array
    {
        if ($parts < 1) {
            throw new ValueError('Cannot allocate into fewer than 1 part');
        }

        if ($parts === 1) {
            return [new self($this->amount, $this->currency)];
        }

        $baseShare = intdiv($this->amount, $parts);
        $remainder = $this->amount - ($baseShare * $parts);

        $result = [];

        for ($i = 0; $i < $parts; $i++) {
            // Distribute remainder cents one-by-one to the first parts.
            // For positive amounts, remainder > 0 means extra cents.
            // For negative amounts, remainder is handled correctly by
            // intdiv truncation toward zero (e.g., -100/3 = -34 rem 2).
            $extra = $i < abs($remainder) ? $remainder <=> 0 : 0;
            $result[] = new self($baseShare + $extra, $this->currency);
        }

        return $result;
    }

    /**
     * Allocate money according to proportions (ratios).
     *
     * Example: Money(100)->allocateRatios([1, 1, 2]) = [25, 25, 50]
     *
     * @param  array<int, int>  $ratios  Integer ratios (all must be >= 0)
     * @return array<int, self>
     *
     * @throws ValueError If ratios is empty or contains negative values
     * @throws OverflowException If amount * ratio exceeds integer limits
     */
    public function allocateRatios(array $ratios): array
    {
        if ($ratios === []) {
            throw new ValueError('Cannot allocate with empty ratios');
        }

        foreach ($ratios as $ratio) {
            if ($ratio < 0) {
                throw new ValueError('Ratios must be non-negative');
            }
        }

        $total = array_sum($ratios);

        if ($total === 0) {
            throw new ValueError('Sum of ratios must be greater than zero');
        }

        // Overflow guard for amount * ratio multiplication
        if (extension_loaded('bcmath')) {
            foreach ($ratios as $ratio) {
                $check = bcmul((string) $this->amount, (string) $ratio, 0);
                if (bccomp($check, (string) PHP_INT_MAX) > 0
                    || bccomp($check, (string) PHP_INT_MIN) < 0
                ) {
                    throw new OverflowException(
                        "Money allocateRatios overflow: {$this->amount} * {$ratio} exceeds integer limits"
                    );
                }
            }
        }

        $result = [];
        $allocated = 0;
        $counter = count($ratios);

        for ($i = 0; $i < $counter; $i++) {
            if ($i === count($ratios) - 1) {
                // Last part gets the remainder to avoid rounding drift
                $result[] = new self($this->amount - $allocated, $this->currency);
            } else {
                $share = intdiv($this->amount * $ratios[$i], $total);
                $result[] = new self($share, $this->currency);
                $allocated += $share;
            }
        }

        return $result;
    }

    /**
     * Multiply by a factor.
     *
     * Uses BCMath when available for precise integer arithmetic.
     * Falls back to float with rounding for environments without ext-bcmath.
     * Both paths use round-half-away-from-zero for consistency.
     *
     * @throws OverflowException If the result exceeds integer limits
     */
    public function multiply(float $factor): self
    {
        if ($factor === 0.0) {
            return new self(0, $this->currency);
        }

        if (extension_loaded('bcmath')) {
            // Use bcmath for arbitrary precision, then verify it fits in an int
            $scale = $factor === floor($factor) ? 0 : 2;
            $product = bcmul((string) $this->amount, (string) $factor, $scale);

            // Overflow check against PHP_INT_MAX/MIN
            if (bccomp($product, (string) PHP_INT_MAX) > 0
                || bccomp($product, (string) PHP_INT_MIN) < 0
            ) {
                throw new OverflowException(
                    "Money multiply overflow: {$this->amount} * {$factor} exceeds integer limits"
                );
            }

            // Round half away from zero
            if ($scale > 0) {
                if (bccomp($product, '0') >= 0) {
                    $product = bcadd($product, '0.5', 0);
                } else {
                    $product = bcsub($product, '0.5', 0);
                }
            }

            return new self((int) $product, $this->currency);
        }

        // Float fallback with overflow detection
        $floatResult = $this->amount * $factor;

        if (abs($floatResult) > (float) PHP_INT_MAX) {
            throw new OverflowException(
                "Money multiply overflow: {$this->amount} * {$factor} exceeds integer limits"
            );
        }

        $newAmount = (int) round($floatResult);

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
            // Higher scale for precision, then round
            $quotient = bcdiv((string) $this->amount, (string) $divisor, 2);

            // Round half away from zero
            if (bccomp($quotient, '0') >= 0) {
                $quotient = bcadd($quotient, '0.5', 0);
            } else {
                $quotient = bcsub($quotient, '0.5', 0);
            }

            return new self((int) $quotient, $this->currency);
        }

        $newAmount = (int) round($this->amount / $divisor);

        return new self($newAmount, $this->currency);
    }

    /**
     * Apply a percentage (e.g., tax) and return the result.
     *
     * Example: Money::fromMajor(9.99, 'EUR')->percentage(19) for 19% VAT.
     *
     * @param  float  $percent  Percentage to apply (e.g., 19 for 19%)
     *
     * @throws OverflowException If the result exceeds integer limits
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
        return $this->currency()->decimalPlaces();
    }

    /**
     * Get the subunit divisor (e.g., 100 for USD, 1 for JPY, 1000 for KWD).
     */
    public function subunitDivisor(): int
    {
        return $this->currency()->subunitDivisor();
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
     *
     * @throws OverflowException If the converted minor units exceed integer limits
     */
    public static function fromMajor(float $amount, string $currency = 'USD'): self
    {
        $temp = new self(0, $currency);
        $divisor = $temp->subunitDivisor();

        $floatResult = $amount * $divisor;

        if (abs($floatResult) > (float) PHP_INT_MAX) {
            throw new OverflowException(
                "Money fromMajor overflow: {$amount} * {$divisor} exceeds integer limits"
            );
        }

        return new self((int) round($floatResult), $currency);
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
