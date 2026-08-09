<?php

/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use ZeroBoiler\ValueObjects\Contracts\ValueObject as ValueObjectContract;

/**
 * Trait to auto-register ValueObjects as Eloquent casts.
 *
 * Include this in your ValueObject subclass to enable automatic
 * cast registration without manual configuration.
 *
 * @since 1.0.0
 *
 * Usage:
 *   use ZeroBoiler\ValueObjects\ValueObject;
 *   use ZeroBoiler\ValueObjects\Castable;
 *
 *   class Money extends ValueObject
 *   {
 *       use Castable;
 *   }
 *
 * Then in your model:
 *   protected $casts = [
 *       'price' => Money::class,  // Auto-registered!
 *   ]
 *
 * @template T of ValueObjectContract
 */
trait Castable
{
    /**
     * Get the cast class name for this ValueObject.
     *
     * @param  array<int|string, mixed>  $arguments
     * @return ValueObjectCast<self>
     */
    public static function castUsing(array $arguments = []): CastsAttributes
    {
        /** @var class-string<self> $class */
        $class = static::class;

        return new ValueObjectCast($class);
    }

    /**
     * Get the cast attributes for this ValueObject.
     *
     * @return array<string, string>
     */
    public static function getCastAttributes(): array
    {
        return [
            static::class => ValueObjectCast::class.':'.static::class,
        ];
    }
}
