<?php
/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects\Exceptions;

use Exception;

/**
 * Base exception for all value-objects domain errors.
 *
 * Provides a consistent root for the value-objects exception hierarchy,
 * enabling callers to catch all value-objects-specific errors with a single
 * catch block while allowing fine-grained handling of specific subtypes.
 *
 *
 * @see \ZeroBoiler\ValueObjects\Exceptions\InvalidValueObjectsArgumentException
 * @see \ZeroBoiler\ValueObjects\Exceptions\ValueObjectsRuntimeException
 * @since 1.26.0
 */
abstract class ValueObjectsException extends Exception
{
    /**
     * Create a value-objects exception with an optional previous cause.
     *
     * @since 1.26.0
     *
     * @param  string  $message  Human-readable error description
     * @param  int  $code  Application-specific error code (default: 0)
     * @param  \Throwable|null  $previous  The exception chain predecessor
     */
    public function __construct(string $message, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
