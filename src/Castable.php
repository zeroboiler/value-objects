<?php

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects;

/**
 * Trait to auto-register ValueObjects as Eloquent casts.
 *
 * Include this in your ValueObject subclass to enable automatic
 * cast registration without manual configuration.
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
 *   ];
 */
trait Castable
{
    /**
     * Get the cast class name for this ValueObject.
     *
     * @return class-string<\ZeroBoiler\ValueObjects\Casts\ValueObjectCast>
     */
    public static function castUsing(): string
    {
        return ValueObjectCast::class;
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