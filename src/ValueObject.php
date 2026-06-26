<?php

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects;

use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Facades\Validator;
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
abstract readonly class ValueObject implements Jsonable, JsonSerializable, Stringable
{
    /**
     * Validate data using Laravel validator.
     *
     * Call this in your constructor to ensure data integrity.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $rules
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validate(array $data, array $rules): void
    {
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            $validator->validate(); // Throws ValidationException
        }
    }

    /**
     * Compare two value objects by value.
     *
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
     */
    public function toJson(int $options = 0): string
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