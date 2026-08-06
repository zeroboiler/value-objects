<?php

/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects;

use Illuminate\Validation\ValidationException;

/**
 * Email value object with normalization and validation.
 */
final class Email extends ValueObject
{
    /** @use Castable<self> */
    use Castable;

    /** Normalized email address (lowercase) */
    public readonly string $value;

    /**
     * @param  string  $email  Email address
     *
     * @throws ValidationException If email is invalid
     */
    public function __construct(string $email)
    {
        $normalized = strtolower(trim($email));

        $this->validate(
            ['email' => $normalized],
            ['email' => 'required|email|max:254']
        );

        $this->value = $normalized;
    }

    /**
     * Get domain part of email.
     */
    public function domain(): string
    {
        $atPos = strrpos($this->value, '@');

        if ($atPos === false) {
            return '';
        }

        return substr($this->value, $atPos + 1);
    }

    /**
     * Get local part (before @) of email.
     */
    public function localPart(): string
    {
        $atPos = strrpos($this->value, '@');

        if ($atPos === false) {
            return $this->value;
        }

        return substr($this->value, 0, $atPos);
    }

    #[\Override]
    public function toArray(): array
    {
        return ['email' => $this->value];
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Get the primitive value for database storage.
     */
    #[\Override]
    #[\Override]
    public function toPrimitive(): mixed
    {
        return $this->value;
    }

    /**
     * Create from a primitive database value (string).
     */
    #[\Override]
    public static function fromPrimitive(mixed $value): static
    {
        if (! is_string($value)) {
            throw new \InvalidArgumentException('Email expects a string, got '.get_debug_type($value));
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
        return 'string';
    }
}
