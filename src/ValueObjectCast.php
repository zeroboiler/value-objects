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
        } catch (JsonException $e) {
            // Log the corruption — returning null silently would hide data loss.
            // We still return null to avoid crashing the app, but the error
            // log surfaces the problem for investigation.
            // Use report() when available (Laravel), otherwise fall back to
            // error_log so the cast works outside a full Laravel app boot.
            $exception = new \RuntimeException(
                "Failed to decode JSON for ValueObjectCast({$this->valueObjectClass}): " . $value,
                0,
                $e
            );

            if (\function_exists('report')) {
                report($exception);
            } else {
                \error_log($exception->getMessage());
            }

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
     * For single-parameter VOs (Email, Url, etc.), pass the first value
     * from the toArray() data directly, since the constructor takes a
     * single scalar.
     *
     * For multi-parameter VOs (Money, Address, Coordinates, etc.), use
     * strict name-based matching: each constructor parameter name must
     * match a key in the toArray() output. This avoids the ambiguity of
     * type-based fuzzy matching.
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

        // Single-param VO: pass the first data value positionally.
        // This handles VOs like Email($email) or Url($url) whose toArray()
        // may include extra computed keys (e.g., scheme, host) that are
        // not constructor arguments.
        if (count($params) === 1) {
            $paramName = $params[0]->getName();

            // Prefer a direct name match if available
            if (array_key_exists($paramName, $data)) {
                return [$paramName => $data[$paramName]];
            }

            // Otherwise pass the first value positionally
            $values = array_values($data);

            return $values === [] ? [] : [$values[0]];
        }

        // Multi-param VO: use strict name-based matching only.
        // Each constructor parameter must find its value by name in the
        // toArray() output. This avoids ambiguity from type-based
        // guessing that could assign the wrong value to the wrong param.
        $args = [];

        foreach ($params as $param) {
            $paramName = $param->getName();

            if (array_key_exists($paramName, $data)) {
                $args[$paramName] = $data[$paramName];
            } elseif ($param->isDefaultValueAvailable()) {
                // Skip params with defaults — the constructor will use
                // the default value.
                continue;
            } elseif ($param->allowsNull()) {
                $args[$paramName] = null;
            }
        }

        return $args;
    }
}
