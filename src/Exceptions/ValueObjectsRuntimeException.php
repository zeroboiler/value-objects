<?php
/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects\Exceptions;

/**
 * Thrown when a runtime operation fails in the value-objects domain.
 *
 * Replaces generic \RuntimeException with a domain-specific exception
 * for better error handling and debugging.
 *
 *
 * @see \ZeroBoiler\ValueObjects\Exceptions\InvalidValueObjectsArgumentException
 * @see \ZeroBoiler\ValueObjects\Exceptions\ValueObjectsException
 * @since 1.27.0
 */
final class ValueObjectsRuntimeException extends ValueObjectsException
{
    /**
     * Create a runtime exception for a generic message.
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

    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null): void
    {
        parent::__construct($message ?: 'Value-objects runtime error.', $code, $previous);
    }
}
