<?php

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects;

use NumberFormatter;
use ValueError;

/**
 * Money value object using integer cents for precision.
 *
 * Uses ISO 4217 currency codes.
 */
final class Money extends ValueObject
{
    use Castable;

    /** Amount in cents (smallest currency unit) */
    public int $amount;

    /** ISO 4217 currency code (e.g., "USD", "EUR") */
    public string $currency;

    /**
     * @param  int  $amount  Amount in cents
     * @param  string  $currency  ISO 4217 currency code
     */
    public function __construct(int $amount, string $currency = 'USD')
    {
        $this->validate(
            ['amount' => $amount, 'currency' => $currency],
            [
                'amount' => 'required|integer',
                'currency' => 'required|string|size:3|uppercase',
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
     */
    public function multiply(float $factor): self
    {
        $newAmount = (int) round($this->amount * $factor);

        return new self($newAmount, $this->currency);
    }

    /**
     * Divide by a divisor.
     *
     * @throws ValueError If divisor is zero
     */
    public function divide(float $divisor): self
    {
        if ($divisor === 0.0) {
            throw new ValueError('Cannot divide money by zero');
        }

        $newAmount = (int) round($this->amount / $divisor);

        return new self($newAmount, $this->currency);
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
     * Format money for display.
     *
     * @param  string|null  $locale  Locale for formatting (e.g., "en_US")
     * @return string Formatted amount (e.g., "$1,234.56")
     */
    public function format(?string $locale = null): string
    {
        $locale ??= config('app.locale', 'en_US');
        $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
        $formatter->setTextAttribute(NumberFormatter::CURRENCY_CODE, $this->currency);

        return $formatter->format($this->amount / 100);
    }

    /**
     * Get amount in major units (dollars, euros, etc.).
     */
    public function toMajor(): float
    {
        return $this->amount / 100;
    }

    /**
     * Create from major units (dollars, euros, etc.).
     *
     * @param  float  $amount  Amount in major units
     */
    public static function fromMajor(float $amount, string $currency = 'USD'): self
    {
        return new self((int) round($amount * 100), $currency);
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