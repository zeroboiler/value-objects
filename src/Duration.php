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
        $totalSeconds = $this->toSeconds();
        $hours = (int) floor($totalSeconds / 3600);
        $minutes = (int) floor(($totalSeconds % 3600) / 60);
        $seconds = (int) floor($totalSeconds % 60);
        $ms = $this->milliseconds % 1000;

        $parts = [];

        if ($hours > 0) {
            $parts[] = $hours === 1 ? '1 hour' : "{$hours} hours";
        }

        if ($minutes > 0) {
            $parts[] = $minutes === 1 ? '1 minute' : "{$minutes} minutes";
        }

        if ($seconds > 0 || $parts === []) {
            $parts[] = $seconds === 1 ? '1 second' : "{$seconds} seconds";
        }

        if ($ms > 0 && count($parts) < 2) {
            $parts[] = "{$ms}ms";
        }

        return implode(' ', $parts);
    }

    public function toArray(): array
    {
        return ['milliseconds' => $this->milliseconds];
    }

    public function __toString(): string
    {
        return $this->humanReadable();
    }
}
