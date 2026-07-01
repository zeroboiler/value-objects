<?php

/**
 * This file is part of ZeroBoiler, licensed under the proprietary license.
 */

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use Stringable;

/**
 * Interface for all ZeroBoiler value objects.
 *
 * Provides a single type to type-hint against when accepting
 * any value object across packages (DTO, persistence, etc.).
 *
 * @template TKey of array-key
 * @template TValue
 *
 * @extends Arrayable<TKey, TValue>
 * @extends Jsonable<int|string>
 */
interface ValueObjectInterface extends Arrayable, Jsonable, JsonSerializable, Stringable
{
    /**
     * Compare this value object with another by value.
     *
     * @return bool True if all attributes are equal
     */
    public function equals(self $other): bool;

    /**
     * Serialize value object to array.
     *
     * @return array<TKey, TValue>
     */
    public function toArray(): array;

    /**
     * Serialize value object to JSON.
     *
     * @param  int  $options  JSON encode options
     */
    public function toJson($options = 0): string;

    /**
     * String representation of the value object.
     */
    public function __toString(): string;
}
