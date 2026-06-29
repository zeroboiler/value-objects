<?php

/**
 * This file is part of ZeroBoiler, licensed under the proprietary license.
 */

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects;

use Illuminate\Container\Container;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Validation\ValidationException;
use JsonSerializable;
use Stringable;

/**
 * Base class for immutable value objects.
 *
 * Value objects represent a value through their attributes, not identity.
 * Two value objects are equal if all their attributes are equal.
 *
 * @template T
 */
abstract class ValueObject implements Jsonable, JsonSerializable, Stringable
{
    /**
     * Validate data using Laravel validator.
     *
     * Call this in your constructor to ensure data integrity.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $rules
     *
     * @throws ValidationException
     */
    protected function validate(array $data, array $rules): void
    {
        $factory = Container::getInstance()->make(ValidationFactory::class);
        $validator = $factory->make($data, $rules);

        if ($validator->fails()) {
            $validator->validate(); // Throws ValidationException
        }
    }

    /**
     * Compare two value objects by value.
     *
     * @param  ValueObject<mixed>  $other
     * @return bool True if all attributes are equal
     */
    public function equals(self $other): bool
    {
        return $this->toArray() === $other->toArray();
    }

    /**
     * Serialize value object to array.
     *
     * @return array<string, mixed>
     */
    abstract public function toArray(): array;

    /**
     * Serialize value object to JSON.
     *
     * @param  int  $options  JSON encode options (JSON_THROW_ON_ERROR, etc.)
     */
    public function toJson($options = 0): string
    {
        return json_encode($this, $options);
    }

    /**
     * Serialize for json_encode().
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * String representation of value object.
     */
    abstract public function __toString(): string;
}
