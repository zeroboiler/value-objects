<?php

/**
 * This file is part of ZeroBoiler, licensed under the proprietary license.
 */

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects;

use Illuminate\Container\Container;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Validation\ValidationException;

/**
 * Base class for immutable value objects.
 *
 * Value objects represent a value through their attributes, not identity.
 * Two value objects are equal if all their attributes are equal.
 *
 * All value objects implement {@see ValueObjectInterface} which serves
 * as the cross-package type for accepting arbitrary value objects.
 *
 * @template TKey of array-key
 * @template TValue
 */
abstract class ValueObject implements ValueObjectInterface
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
     * @return bool True if all attributes are equal
     */
    public function equals(ValueObjectInterface $other): bool
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
