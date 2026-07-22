<?php

/**
 * This file is part of ZeroBoiler, licensed under the proprietary license.
 */

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects;

use Illuminate\Container\Container;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Validation\ValidationException;
use ZeroBoiler\ValueObjects\Contracts\ValueObject as ValueObjectContract;

/**
 * Base class for immutable value objects.
 *
 * Value objects represent a value through their attributes, not identity.
 * Two value objects are equal if all their attributes are equal.
 *
 * All value objects implement {@see ValueObjectContract} which serves
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
     * Get the primitive value for database storage.
     *
     * Default implementation returns the JSON-encoded array representation.
     * Single-value VOs should override this to return the scalar directly.
     */
    public function toPrimitive(): mixed
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }

    /**
     * Create an instance from a primitive database value.
     *
     * Default implementation attempts JSON decode then uses PHP's
     * natural spread behavior: sequential arrays become positional
     * arguments, associative arrays become named arguments.
     * Single-value VOs should override this.
     *
     * @throws \InvalidArgumentException If reconstruction fails
     * @throws ValidationException If validation fails
     */
    public static function fromPrimitive(mixed $value): static
    {
        // If the value is already an array, spread it directly.
        // PHP handles sequential arrays as positional args and
        // associative arrays as named args (#6).
        if (is_array($value)) {
            return new static(...$value);
        }

        // If it's JSON, decode and spread
        if (is_string($value)) {
            $decoded = json_decode($value, true, 512);

            if (is_array($decoded)) {
                return new static(...$decoded);
            }

            // Scalar string — pass to constructor
            return new static($value);
        }

        // Numeric or other scalar
        return new static($value);
    }

    /**
     * Get the SQL column type for SchemaBuilder / migrations.
     *
     * Default is 'json' for composite VOs. Single-value VOs should
     * override to return the appropriate scalar type.
     *
     * @return non-empty-string
     */
    public static function columnType(): string
    {
        return 'json';
    }

    /**
     * Compare this value object with another by value.
     *
     * @return bool True if all attributes are equal
     */
    public function equals(?ValueObjectContract $other): bool
    {
        if (! $other instanceof ValueObjectContract) {
            return false;
        }

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
