<?php

/**
 * This file is part of ZeroBoiler, licensed under the proprietary license.
 */

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use JsonException;

/**
 * Universal value object cast for Eloquent models.
 *
 * Works with any ValueObject subclass that can be instantiated
 * from an array via the constructor.
 *
 * Usage:
 *   protected $casts = [
 *       'price' => \ZeroBoiler\ValueObjects\Casts\ValueObjectCast::class.':'.Money::class,
 *   ];
 *
 * Or use the Castable trait for auto-registration:
 *   protected $casts = [
 *       'price' => Money::class,
 *   ];
 *
 * @template T of ValueObject
 *
 * @implements CastsAttributes<T|null, T|null>
 */
class ValueObjectCast implements CastsAttributes
{
    /**
     * @param  class-string<T>  $valueObjectClass
     */
    public function __construct(private readonly string $valueObjectClass) {}

    /**
     * Cast DB value to ValueObject instance.
     *
     * @param  Model  $model
     * @param  string|null  $value
     * @param  array<string, mixed>  $attributes
     * @return T|null
     */
    public function get($model, string $key, $value, array $attributes)
    {
        if ($value === null) {
            return null;
        }

        try {
            $data = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        if (! is_array($data)) {
            return null;
        }

        return new ($this->valueObjectClass)(...$data);
    }

    /**
     * Transform ValueObject to DB-storable JSON.
     *
     * @param  Model  $model
     * @param  T|null  $value
     * @param  array<string, mixed>  $attributes
     * @return string|null
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if ($value === null) {
            return null;
        }

        return json_encode($value);
    }

    /**
     * Serialize ValueObject for JSON resources.
     *
     * @param  Model  $model
     * @param  T|null  $value
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>|null
     */
    public function serialize($model, string $key, $value, array $attributes)
    {
        return $value?->toArray();
    }
}
