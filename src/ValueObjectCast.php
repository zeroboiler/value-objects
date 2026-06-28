<?php

/**
 * This file is part of ZeroBoiler, licensed under the proprietary license.
 */

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use JsonException;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * Universal value object cast for Eloquent models.
 *
 * Works with any ValueObject subclass by reconstructing it from
 * its constructor parameters using reflection.
 *
 * Usage:
 *   protected $casts = [
 *       'price' => \ZeroBoiler\ValueObjects\ValueObjectCast::class.':'.Money::class,
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
    /** @var array<string, ReflectionParameter[]> */
    private static array $constructorCache = [];

    /**
     * @param  class-string<T>  $valueObjectClass
     */
    public function __construct(private readonly string $valueObjectClass) {}

    /**
     * Cast DB value to ValueObject instance.
     *
     * Reconstructs the ValueObject by mapping toArray() keys back to
     * constructor parameters, handling both positional and named args.
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

        // Try named argument reconstruction first
        $args = $this->mapToArrayKeys($data);

        return new ($this->valueObjectClass)(...$args);
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

    /**
     * Map toArray() associative keys back to constructor parameter names.
     *
     * Each ValueObject's toArray() returns keys like ['email' => '...'] or
     * ['amount' => 100, 'currency' => 'USD']. The constructor may use
     * different parameter names (e.g., $email vs $value). This method
     * resolves the mapping by:
     * 1. Direct name match (toArray key === param name)
     * 2. Positional fallback (order-based)
     *
     * @param  array<string, mixed>  $data
     * @return array<int|string, mixed> Named/positional arguments for constructor
     */
    private function mapToArrayKeys(array $data): array
    {
        $class = $this->valueObjectClass;

        if (! isset(self::$constructorCache[$class])) {
            $reflection = new ReflectionClass($class);
            $constructor = $reflection->getConstructor();
            self::$constructorCache[$class] = $constructor !== null
                ? $constructor->getParameters()
                : [];
        }

        $params = self::$constructorCache[$class];

        if ($params === []) {
            // No constructor params — pass values as positional args
            return array_values($data);
        }

        $args = [];
        $remainingData = $data;

        foreach ($params as $param) {
            $paramName = $param->getName();

            // 1. Direct match: toArray key matches param name
            if (array_key_exists($paramName, $remainingData)) {
                $args[$paramName] = $remainingData[$paramName];
                unset($remainingData[$paramName]);

                continue;
            }

            // 2. Type-based match: find a value in remaining data that
            //    matches the parameter type
            $type = $param->getType();
            if ($type instanceof ReflectionNamedType) {
                foreach ($remainingData as $dataKey => $dataValue) {
                    if ($this->matchesType($type->getName(), $dataValue)) {
                        $args[$paramName] = $dataValue;
                        unset($remainingData[$dataKey]);

                        continue 2;
                    }
                }
            }
        }

        return $args;
    }

    /**
     * Check if a value matches a PHP type.
     */
    private function matchesType(string $expectedType, mixed $value): bool
    {
        return match ($expectedType) {
            'string' => is_string($value),
            'int', 'integer' => is_int($value),
            'float', 'double' => is_float($value) || is_int($value),
            'bool', 'boolean' => is_bool($value),
            'array' => is_array($value),
            default => true, // Mixed or class type — assume valid
        };
    }
}
