<?php

/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects\Contracts;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Validation\ValidationException;
use JsonSerializable;
use Stringable;
use ZeroBoiler\ValueObjects\ValueObjectInterface;

/**
 * Contract for all ZeroBoiler value objects.
 *
 * This is the canonical interface for type-hinting "any value object"
 * across packages. DTO, persistence, search, and other packages should
 * type-hint against this interface rather than concrete VO classes.
 *
 * The legacy {@see ValueObjectInterface} extends
 * this contract for backward compatibility.
 *
 * @extends Arrayable<array-key, mixed>
 */
interface ValueObject extends Arrayable, Jsonable, JsonSerializable, Stringable
{
    /**
     * Get the primitive value for database storage.
     *
     * For single-value VOs (Email, Url, PhoneNumber), this returns the
     * primary scalar (string). For composite VOs (Money, Address), this
     * returns the JSON-encoded array representation.
     *
     * @return mixed The primitive value suitable for DB storage
     */
    public function toPrimitive(): mixed;

    /**
     * Create an instance from a primitive database value.
     *
     * This is the inverse of {@see toPrimitive()}. It accepts whatever
     * {@see toPrimitive()} produces and reconstructs the VO.
     *
     * @param  mixed  $value  The primitive value from the database
     *
     * @throws \InvalidArgumentException If the value cannot be reconstructed
     * @throws ValidationException If the value fails validation
     */
    public static function fromPrimitive(mixed $value): static;

    /**
     * Compare this value object with another for equality.
     *
     * Two value objects are equal if all their attributes are equal.
     *
     * @param  self|null  $other  The value object to compare with
     * @return bool True if both VOs have the same value
     */
    public function equals(?self $other): bool;

    /**
     * Get the SQL column type for SchemaBuilder / migrations.
     *
     * Returns a string like 'string', 'integer', 'decimal', 'json',
     * 'float', 'boolean' that maps to Laravel migration column types.
     *
     * @return non-empty-string The column type for database schema
     */
    public static function columnType(): string;
}
