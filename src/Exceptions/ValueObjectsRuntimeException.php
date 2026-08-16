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
}
