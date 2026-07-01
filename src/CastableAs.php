<?php

/**
 * This file is part of ZeroBoiler, licensed under the proprietary license.
 */

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects;

use Attribute;

/**
 * Attribute for declaring explicit ValueObject reconstruction for Eloquent casts.
 *
 * Solves the ambiguity in {@see ValueObjectCast} when a ValueObject's
 * constructor signature differs from its `toArray()` output shape.
 *
 * When a VO has this attribute, ValueObjectCast uses the declared methods
 * instead of the reflection-based heuristic.
 *
 * Usage:
 *   #[CastableAs(fromArray: 'fromDatabase', toArray: 'toDatabase')]
 *   class Money extends ValueObject
 *   {
 *       public static function fromDatabase(array $data): static { ... }
 *       public function toDatabase(): string { ... }
 *   }
 *
 * @see ValueObjectCast
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class CastableAs
{
    /**
     * @param  string|null  $fromArray  Static method name: `public static function(array $data): static`
     * @param  string|null  $toArray  Instance method name: `public function(): mixed` (string for DB storage)
     */
    public function __construct(
        public ?string $fromArray = null,
        public ?string $toArray = null,
    ) {}
}
