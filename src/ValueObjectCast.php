<?php
/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use JsonException;
use ReflectionClass;
use ReflectionParameter;
use RuntimeException;
use ZeroBoiler\ValueObjects\Contracts\ValueObject as ValueObjectContract;

/**
 * Universal value object cast for Eloquent models.
 *
 * Works with any ValueObject subclass by reconstructing it from
 * its constructor parameters using reflection, or via explicit
 * {@see CastableAs} attribute methods.
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
 * For VOs with non-trivial reconstruction (e.g., constructor differs from
 * toArray shape), add a #[CastableAs] attribute to the VO class:
 *
 *   #[CastableAs(fromArray: 'fromCast', toArray: 'toCast')]
 *   class MyVo extends ValueObject { ... }
 *
 * @template T of ValueObjectContract
 *
 * @implements CastsAttributes<T|null, T|null>
 *
 * @since 1.0.0
 */
final class ValueObjectCast implements CastsAttributes
{
    /** @var array<string, ReflectionParameter[]> */
    private static array $constructorCache = [];

    /** @var array<string, CastableAs|null> */
    private static array $castableCache = [];

    /**
     * @param  class-string<T>  $valueObjectClass
     */
    public function __construct(private readonly string $valueObjectClass): void {}

    /**
     * Cast DB value to ValueObject instance.
     *
     * Reconstructs the ValueObject using one of these strategies (first match wins):
     * 1. If the VO has a #[CastableAs] attribute, use its `fromArray` method.
     * 2. Otherwise, map toArray() keys back to constructor parameters via reflection.
     *
     * @param  Model  $model
     * @param  string|null  $value
     * @param  array<string, mixed>  $attributes
     * @return T|null
     *
     * @throws RuntimeException If the stored JSON cannot be decoded (#659).
     *                          Previously this returned null silently, hiding data corruption.
     *                          Now it throws so developers can catch and fix the root cause.
     *                          If you need graceful degradation, wrap your model access in try/catch.
     *
     * @since 1.0.0
     */
    public function get($model, string $key, $value, array $attributes): mixed
    {
        if ($value === null) {
            return null;
        }

        // Strategy 1: Check for #[CastableAs] attribute — explicit reconstruction
        $castable = $this->resolveCastable();

        if ($castable instanceof CastableAs && $castable->fromArray !== null) {
            $method = $castable->fromArray;

            // The DB value might be JSON (from set()) or a plain scalar.
            // Try JSON first, fall back to passing the raw value.
            try {
                $data = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                // Not JSON — pass the raw string to the factory method.
                return ($this->valueObjectClass)::$method($value);
            }

            if (is_array($data)) {
                return ($this->valueObjectClass)::$method($data);
            }

            return ($this->valueObjectClass)::$method($value);
        }

        // Strategy 2: Reflection-based reconstruction
        try {
            $data = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            // #659: Throw instead of silently returning null.
            // Silent null hides data corruption — the developer needs to know.
            throw new \ZeroBoiler\ValueObjects\Exceptions\ValueObjectsRuntimeException(
                "Failed to decode JSON for ValueObjectCast({$this->valueObjectClass}): ".$value,
                0,
                $e
            );
        }

        if (! is_array($data)) {
            // Scalar value — use decoded value (json_decode already unwrapped quotes)
            return new ($this->valueObjectClass)($data);
        }

        $args = $this->mapToArrayKeys($data);

        return new ($this->valueObjectClass)(...$args);
    }

    /**
     * Transform ValueObject to DB-storable value.
     *
     * If the VO has a #[CastableAs] attribute with a `toArray` method,
     * uses that method. Otherwise, serializes as JSON via json_encode().
     *
     * @param  Model  $model
     * @param  T|null  $value
     * @param  array<string, mixed>  $attributes
     * @return string|null
     *
     * @since 1.0.0
     */
    public function set($model, string $key, $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        $castable = $this->resolveCastable();

        if ($castable instanceof CastableAs && $castable->toArray !== null) {
            $method = $castable->toArray;

            return (string) $value->$method();
        }

        return json_encode($value, JSON_THROW_ON_ERROR);
    }

    /**
     * Serialize ValueObject for JSON resources.
     *
     * @param  Model  $model
     * @param  T|null  $value
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>|null
     *
     * @since 1.0.0
     */
    public function serialize($model, string $key, $value, array $attributes): ?array
    {
        return $value?->toArray();
    }

    /**
     * Resolve the CastableAs attribute for the target class, if present.
     */
    private function resolveCastable(): ?CastableAs
    {
        $class = $this->valueObjectClass;

        if (! isset(self::$castableCache[$class])) {
            $reflection = new ReflectionClass($class);
            $attributes = $reflection->getAttributes(CastableAs::class);
            self::$castableCache[$class] = $attributes !== [] ? $attributes[0]->newInstance() : null;
        }

        return self::$castableCache[$class];
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
     * match a key in the toArray() output.
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
            return array_values($data);
        }

        // Single-param VO: prefer exact param name match, fallback to first value
        if (count($params) === 1) {
            $paramName = $params[0]->getName();

            if (array_key_exists($paramName, $data)) {
                return [$paramName => $data[$paramName]];
            }

            $values = array_values($data);

            return $values === [] ? [] : [$values[0]];
        }

        // Multi-param VO: strict name-based matching
        $args = [];

        foreach ($params as $param) {
            $paramName = $param->getName();

            if (array_key_exists($paramName, $data)) {
                $args[$paramName] = $data[$paramName];
            } elseif ($param->isDefaultValueAvailable()) {
                continue;
            } elseif ($param->allowsNull()) {
                $args[$paramName] = null;
            }
        }

        return $args;
    }
}
