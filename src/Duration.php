<?php

/**
 * This file is part of ZeroBoiler, licensed under the proprietary license.
 */

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects;

/**
 * Duration value object in milliseconds.
 *
 * @extends ValueObject<array<string, mixed>>
 */
final class Duration extends ValueObject
{
    /** @use Castable<self> */
    use Castable;

    /** Duration in milliseconds */
    public readonly int $milliseconds;

    /**
     * @param  int  $milliseconds  Duration in milliseconds (can be negative for differences)
     */
    public function __construct(int $milliseconds)
    {
        $this->validate(
            ['milliseconds' => $milliseconds],
            ['milliseconds' => 'required|integer']
        );

        $this->milliseconds = $milliseconds;
    }

    /**
     * Create from seconds.
     */
    public static function fromSeconds(int|float $seconds): self
    {
        return new self((int) round($seconds * 1000));
    }

    /**
     * Create from minutes.
     */
    public static function fromMinutes(int|float $minutes): self
    {
        return new self((int) round($minutes * 60 * 1000));
    }

    /**
     * Create from hours.
     */
    public static function fromHours(int|float $hours): self
    {
        return new self((int) round($hours * 60 * 60 * 1000));
    }

    /**
     * Get duration in seconds.
     *
     * @return float Seconds (may be fractional)
     */
    public function toSeconds(): float
    {
        return $this->milliseconds / 1000;
    }

    /**
     * Get duration in minutes.
     *
     * @return float Minutes (may be fractional)
     */
    public function toMinutes(): float
    {
        return $this->toSeconds() / 60;
    }

    /**
     * Get duration in hours.
     *
     * @return float Hours (may be fractional)
     */
    public function toHours(): float
    {
        return $this->toMinutes() / 60;
    }

    /**
     * Add another duration.
     */
    public function add(self $other): self
    {
        return new self($this->milliseconds + $other->milliseconds);
    }

    /**
     * Subtract another duration.
     *
     * Allows negative results to surface logic errors rather than
     * silently clamping to zero. Use clamp() if non-negative is required.
     *
     * @throws \ValueError If result would be negative (set $allowNegative to false)
     */
    public function subtract(self $other, bool $allowNegative = true): self
    {
        $result = $this->milliseconds - $other->milliseconds;

        if ($result < 0 && ! $allowNegative) {
            throw new \ValueError(
                'Duration subtraction results in a negative value ('.$result.'ms). '.
                'Pass $allowNegative=true or check inputs.'
            );
        }

        return new self($result);
    }

    /**
     * Clamp duration to a minimum of zero.
     */
    public function clampToZero(): self
    {
        return new self(max(0, $this->milliseconds));
    }

    /**
     * Get human-readable string.
     *
     * @return string e.g., "2 hours 15 minutes 30 seconds"
     */
    public function humanReadable(): string
    {
        $isNegative = $this->milliseconds < 0;
        $totalSeconds = abs($this->toSeconds());
        $ms = abs($this->milliseconds) % 1000;
        $hours = (int) floor($totalSeconds / 3600);
        $remaining = fmod($totalSeconds, 3600);
        $minutes = (int) floor($remaining / 60);
        $seconds = (int) floor(fmod($remaining, 60));

        $parts = [];

        if ($hours > 0) {
            $parts[] = $hours === 1 ? '1 hour' : "{$hours} hours";
        }

        if ($minutes > 0) {
            $parts[] = $minutes === 1 ? '1 minute' : "{$minutes} minutes";
        }

        if ($seconds > 0 || ($parts === [] && $ms === 0)) {
            $parts[] = $seconds === 1 ? '1 second' : "{$seconds} seconds";
        }

        if ($ms > 0) {
            $parts[] = "{$ms}ms";
        }

        $result = implode(' ', $parts);

        return $isNegative ? "-{$result}" : $result;
    }

    public function toArray(): array
    {
        return ['milliseconds' => $this->milliseconds];
    }

    public function __toString(): string
    {
        return $this->humanReadable();
    }

    /**
     * Get the primitive value for database storage.
     */
    #[\Override]
    public function toPrimitive(): mixed
    {
        return $this->milliseconds;
    }

    /**
     * Create from a primitive database value (integer milliseconds).
     */
    #[\Override]
    public static function fromPrimitive(mixed $value): static
    {
        if (! is_int($value)) {
            // Allow numeric strings
            if (is_string($value) && is_numeric($value)) {
                return new self((int) $value);
            }

            throw new \InvalidArgumentException(
                'Duration expects an integer (milliseconds), got '.get_debug_type($value)
            );
        }

        return new self($value);
    }

    /**
     * Get the SQL column type for migrations.
     *
     * @return non-empty-string
     */
    #[\Override]
    public static function columnType(): string
    {
        return 'integer';
    }
}
