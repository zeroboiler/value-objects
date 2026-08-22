<?php
/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects\Exceptions;

/**
 * Thrown when an invalid argument is passed to a value-objects method.
 *
 * Replaces generic \InvalidArgumentException with a domain-specific
 * exception for better error handling and debugging.
 *
 *
 * @see \ZeroBoiler\ValueObjects\Exceptions\ValueObjectsException
 * @see \ZeroBoiler\ValueObjects\Exceptions\ValueObjectsRuntimeException
 * @since 1.26.0
 */
final class InvalidValueObjectsArgumentException extends ValueObjectsException
{
    /**
     * Create an argument exception for a generic message.
     *
     * @since 1.26.0
     *
     * @param  string  $message  Human-readable error description
     * @param  int  $code  Application-specific error code
     * @param  \Throwable|null  $previous  The exception chain predecessor
     * @return self
     */
    public static function forMessage(string $message, int $code = 0, ?\Throwable $previous = null): self
    {
        return new self($message, $code, $previous);
    }

    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message ?: 'Invalid value-objects argument.', $code, $previous);
    }
}
