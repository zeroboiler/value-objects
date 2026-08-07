<?php

/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects;

/**
 * Duration value object in milliseconds.
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
    public function __construct(int $milliseconds): void
: void {
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
     * Create from days.
     */
    public static function fromDays(int|float $days): self
    {
        return new self((int) round($days * 24 * 60 * 60 * 1000));
    }

    /**
     * Parse a human-readable duration string into a Duration value object.
     *
     * Supports combinations of: days (d), hours (h), minutes (m), seconds (s), milliseconds (ms).
     * Examples: "1h 30m", "2d 4h", "500ms", "90s", "1d 2h 30m 45s 500ms".
     * Whitespace between units is optional. Negative durations: "-1h 30m".
     *
     * @param  string  $input  Human-readable duration string
     *
     * @throws \InvalidArgumentException If the string cannot be parsed
     */
    public static function fromHuman(string $input): self
    {
        $trimmed = trim($input);

        if ($trimmed === '') {
            throw new \InvalidArgumentException('Duration::fromHuman() received an empty string');
        }

        $isNegative = str_starts_with($trimmed, '-');
        if ($isNegative) {
            $trimmed = ltrim($trimmed, '-');
        }

        // Match all unit-value pairs: number followed by unit (ms must be matched before m)
        $pattern = '/(\d+)\s*(ms|s|m|h|d)/i';

        if (! preg_match_all($pattern, $trimmed, $matches, PREG_SET_ORDER)) {
            throw new \InvalidArgumentException(
                "Duration::fromHuman() could not parse '{$input}'. "
                .'Expected format like "1h 30m", "2d 4h", "500ms", "90s".'
            );
        }

        // Verify the entire string (minus signs/spaces) was consumed
        $reconstructed = implode(' ', array_map(fn (array $m): string => $m[0], $matches));
        $strippedInput = preg_replace('/[\s,]+/', '', $trimmed);
        $strippedReconstructed = preg_replace('/[\s,]+/', '', $reconstructed);

        if (strtolower($strippedInput) !== strtolower($strippedReconstructed)) {
            throw new \InvalidArgumentException(
                "Duration::fromHuman() could not fully parse '{$input}'. "
                .'Unrecognized segments remain. Expected format: "1h 30m", "2d 4h", "500ms".'
            );
        }

        $totalMs = 0;

        foreach ($matches as $match) {
            $value = (int) $match[1];
            $unit = strtolower($match[2]);

            $totalMs += match ($unit) {
                'ms' => $value,
                's' => $value * 1000,
                'm' => $value * 60 * 1000,
                'h' => $value * 60 * 60 * 1000,
                'd' => $value * 24 * 60 * 60 * 1000,
                default => 0,
            };
        }

        return new self($isNegative ? -$totalMs : $totalMs);
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
     * Get duration in days.
     *
     * @return float Days (may be fractional)
     */
    public function toDays(): float
    {
        return $this->toHours() / 24;
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
     * By default, negative results are allowed to surface logic errors
     * rather than silently clamping to zero. Pass $allowNegative=false
     * to throw a ValueError instead.
     *
     * @throws \ValueError If $allowNegative is false and result would be negative
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
        $days = (int) floor($totalSeconds / 86400);
        $remaining = fmod($totalSeconds, 86400);
        $hours = (int) floor($remaining / 3600);
        $remaining = fmod($remaining, 3600);
        $minutes = (int) floor($remaining / 60);
        $seconds = (int) floor(fmod($remaining, 60));

        $parts = [];

        if ($days > 0) {
            $parts[] = $days === 1 ? '1 day' : "{$days} days";
        }

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

    #[\Override]
    public function toArray(): array
    {
        return ['milliseconds' => $this->milliseconds];
    }

    #[\Override]
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
