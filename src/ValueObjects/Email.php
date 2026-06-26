<?php

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects;

use Illuminate\Validation\ValidationException;

/**
 * Email value object with normalization and validation.
 */
final class Email extends ValueObject
{
    use Castable;

    /** Normalized email address (lowercase) */
    public string $value;

    /**
     * @param  string  $email  Email address
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
        return substr(strrchr($this->value, '@'), 1);
    }

    /**
     * Get local part (before @) of email.
     */
    public function localPart(): string
    {
        return substr($this->value, 0, strrpos($this->value, '@'));
    }

    public function toArray(): array
    {
        return ['email' => $this->value];
    }

    public function __toString(): string
    {
        return $this->value;
    }
}