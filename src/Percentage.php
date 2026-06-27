<?php

/**
 * This file is part of ZeroBoiler, licensed under the proprietary license.
 */

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects;

use ValueError;

/**
 * Percentage value object (0-100).
 */
final class Percentage extends ValueObject
{
    use Castable;

    /** Percentage value (0-100) */
    public float $value;

    /**
     * @param  float  $value  Percentage value (0-100)
     *
     * @throws ValueError If value is outside 0-100 range
     */
    public function __construct(float $value)
    {
        $this->validate(
            ['value' => $value],
            [
                'value' => 'required|numeric|min:0|max:100',
            ]
        );

        $this->value = $value;
    }

    /**
     * Calculate this percentage of a number.
     *
     * @param  float  $number  The number to calculate percentage of
     * @return float The calculated value
     *
     * @example Percentage(25)->of(100) // returns 25.0
     */
    public function of(float $number): float
    {
        return ($this->value / 100) * $number;
    }

    /**
     * Apply this percentage to an amount (alias of of()).
     *
     * @param  float  $amount  The amount to apply percentage to
     * @return float The calculated value
     */
    public function applyTo(float $amount): float
    {
        return $this->of($amount);
    }

    /**
     * Add percentage to this value.
     */
    public function add(self $other): self
    {
        $newValue = min(100, max(0, $this->value + $other->value));

        return new self($newValue);
    }

    /**
     * Subtract percentage from this value.
     */
    public function subtract(self $other): self
    {
        $newValue = min(100, max(0, $this->value - $other->value));

        return new self($newValue);
    }

    /**
     * Multiply by factor (clamped to 0-100).
     */
    public function multiply(float $factor): self
    {
        $newValue = min(100, max(0, $this->value * $factor));

        return new self($newValue);
    }

    /**
     * Check if percentage is zero.
     */
    public function isZero(): bool
    {
        return $this->value === 0.0;
    }

    /**
     * Check if percentage is 100%.
     */
    public function isFull(): bool
    {
        return $this->value === 100.0;
    }

    public function toArray(): array
    {
        return ['value' => $this->value];
    }

    public function __toString(): string
    {
        return number_format($this->value, $this->isDecimal() ? 2 : 0).'%';
    }

    /**
     * Check if value has decimal part.
     */
    private function isDecimal(): bool
    {
        return abs($this->value - round($this->value)) > 0.0001;
    }
}
