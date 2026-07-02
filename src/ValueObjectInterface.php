<?php

/**
 * This file is part of ZeroBoiler, licensed under the proprietary license.
 */

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects;

use ZeroBoiler\ValueObjects\Contracts\ValueObject;
use ZeroBoiler\ValueObjects\Contracts\ValueObject as ValueObjectContract;

/**
 * Legacy interface for all ZeroBoiler value objects.
 *
 * @deprecated Use {@see ValueObject} instead.
 *             This interface extends the contract for backward compatibility
 *             and will be removed in v1.0. Migrate your type-hints to the
 *             Contracts namespace.
 *
 * @template TKey of array-key
 * @template TValue
 *
 * @extends ValueObjectContract<TKey, TValue>
 */
interface ValueObjectInterface extends ValueObjectContract
{
    // All methods inherited from ValueObjectContract.
    // This interface exists solely for backward compatibility.
}
