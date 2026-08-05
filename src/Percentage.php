<?php

/**
 * This file is part of ZeroBoiler, licensed under the proprietary license.
 */

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects;

/**
 * Percentage value object (0-100).
 */
final class Percentage extends ValueObject
{
    /** @use Castable<self> */
    use Castable;

    /** Percentage value (0-100) */
    public readonly float $value;

    /**
     * @param  float  $value  Percentage value (0-100)
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
     *
     * @throws \ValueError If the result exceeds 0-100 range
     */
    public function add(self $other): self
    {
        $newValue = $this->value + $other->value;

        if ($newValue > 100 || $newValue < 0) {
            throw new \ValueError(
                sprintf('Percentage addition overflow: %.2f + %.2f = %.2f exceeds 0-100 range', $this->value, $other->value, $newValue)
            );
        }

        return new self($newValue);
    }

    /**
     * Subtract percentage from this value.
     *
     * @throws \ValueError If the result exceeds 0-100 range
     */
    public function subtract(self $other): self
    {
        $newValue = $this->value - $other->value;

        if ($newValue > 100 || $newValue < 0) {
            throw new \ValueError(
                sprintf('Percentage subtraction overflow: %.2f - %.2f = %.2f exceeds 0-100 range', $this->value, $other->value, $newValue)
            );
        }

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
        return abs($this->value) < 0.0001;
    }

    /**
     * Check if percentage is 100%.
     */
    public function isFull(): bool
    {
        return abs($this->value - 100.0) < 0.0001;
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
     * Get the primitive value for database storage.
     */
    #[\Override]
    public function toPrimitive(): mixed
    {
        return $this->value;
    }

    /**
     * Create from a primitive database value (float).
     */
    #[\Override]
    public static function fromPrimitive(mixed $value): static
    {
        if (is_int($value)) {
            return new self((float) $value);
        }

        if (is_float($value)) {
            return new self($value);
        }

        if (is_string($value) && is_numeric($value)) {
            return new self((float) $value);
        }

        throw new \InvalidArgumentException(
            'Percentage expects a numeric value, got '.get_debug_type($value)
        );
    }

    /**
     * Get the SQL column type for migrations.
     *
     * @return non-empty-string
     */
    #[\Override]
    public static function columnType(): string
    {
        return 'float';
    }

    /**
     * Check if value has decimal part.
     */
    private function isDecimal(): bool
    {
        return abs($this->value - round($this->value)) > 0.0001;
    }
}
