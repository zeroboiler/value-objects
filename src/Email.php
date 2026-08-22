<?php
/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects;

use Illuminate\Validation\ValidationException;

/**
 * Email value object with normalization and validation.
 *
 * @since 1.0.0
 */
final class Email extends ValueObject
{
    /** @use Castable<self> */
    use Castable;

    /** Normalized email address (lowercase) */
    public string $value;

    /**
     * @since 1.0.0
     *
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
     *
     * @since 1.0.0
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
     *
     * @since 1.0.0
     */
    public function localPart(): string
    {
        $atPos = strrpos($this->value, '@');

        if ($atPos === false) {
            return $this->value;
        }

        return substr($this->value, 0, $atPos);
    }

    /**
     * @since 1.0.0
     */
    public function toArray(): array
    {
        return ['email' => $this->value];
    }

    /**
     * @since 1.0.0
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Get the primitive value for database storage.
     *
     * @since 1.0.0
     *
     * @return mixed The email address as a string
     */
    public function toPrimitive(): mixed
    {
        return $this->value;
    }

    /**
     * Create from a primitive database value (string).
     *
     * @since 1.0.0
     */
    public static function fromPrimitive(mixed $value): static
    {
        if (! is_string($value)) {
            throw new \ZeroBoiler\ValueObjects\Exceptions\InvalidValueObjectsArgumentException('Email expects a string, got '.get_debug_type($value));
        }

        return new self($value);
    }

    /**
     * Get the SQL column type for migrations.
     *
     * @since 1.0.0
     *
     * @return non-empty-string
     */
    public static function columnType(): string
    {
        return 'string';
    }
}
