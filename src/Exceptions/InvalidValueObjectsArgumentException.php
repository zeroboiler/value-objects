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
 * @see ValueObjectsException
 *
 * @since 1.26.0
 */
final class InvalidValueObjectsArgumentException extends ValueObjectsException
{
}
