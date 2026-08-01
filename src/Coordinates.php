<?php

/**
 * This file is part of ZeroBoiler, licensed under the proprietary license.
 */

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects;

use ValueError;

/**
 * Geographic coordinates value object (latitude and longitude).
 *
 * @extends ValueObject<array<string, mixed>>
 */
final class Coordinates extends ValueObject
{
    /** @use Castable<self> */
    use Castable;

    /** Latitude in decimal degrees (-90 to 90) */
    public readonly float $latitude;

    /** Longitude in decimal degrees (-180 to 180) */
    public readonly float $longitude;

    /**
     * @param  float  $latitude  Latitude (-90 to 90)
     * @param  float  $longitude  Longitude (-180 to 180)
     *
     * @throws ValueError If coordinates are invalid
     */
    public function __construct(float $latitude, float $longitude)
    {
        if (! $this->isValidLat($latitude)) {
            throw new ValueError("Invalid latitude: {$latitude}. Must be between -90 and 90.");
        }

        if (! $this->isValidLng($longitude)) {
            throw new ValueError("Invalid longitude: {$longitude}. Must be between -180 and 180.");
        }

        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    /**
     * Check if latitude is valid.
     */
    public function isValidLat(float $lat): bool
    {
        return $lat >= -90 && $lat <= 90;
    }

    /**
     * Check if longitude is valid.
     */
    public function isValidLng(float $lng): bool
    {
        return $lng >= -180 && $lng <= 180;
    }

    /**
     * Calculate distance to another coordinate using Haversine formula.
     *
     * @param  self  $other  Target coordinates
     * @return float Distance in meters
     */
    public function distanceTo(self $other): float
    {
        $earthRadius = 6371000; // Earth radius in meters

        $lat1Rad = deg2rad($this->latitude);
        $lat2Rad = deg2rad($other->latitude);
        $deltaLat = deg2rad($other->latitude - $this->latitude);
        $deltaLng = deg2rad($other->longitude - $this->longitude);

        $a = sin($deltaLat / 2) ** 2
            + cos($lat1Rad) * cos($lat2Rad)
            * sin($deltaLng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Get distance in kilometers.
     */
    public function distanceToKm(self $other): float
    {
        return $this->distanceTo($other) / 1000;
    }

    /**
     * Get distance in miles.
     */
    public function distanceToMiles(self $other): float
    {
        return $this->distanceTo($other) / 1609.344;
    }

    public function toArray(): array
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }

    public function __toString(): string
    {
        return "({$this->latitude}, {$this->longitude})";
    }

    /**
     * Get the primitive value for database storage.
     */
    #[\Override]
    public function toPrimitive(): mixed
    {
        return json_encode([
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ], JSON_THROW_ON_ERROR);
    }

    /**
     * Create from a primitive database value.
     *
     * Accepts JSON string, array with latitude/longitude keys, or
     * comma-separated string like "40.7128,-74.0060".
     */
    #[\Override]
    public static function fromPrimitive(mixed $value): static
    {
        if (is_array($value)) {
            return new self(
                (float) ($value['latitude'] ?? 0),
                (float) ($value['longitude'] ?? 0)
            );
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true, 512);

            if (is_array($decoded)) {
                return new self(
                    (float) ($decoded['latitude'] ?? 0),
                    (float) ($decoded['longitude'] ?? 0)
                );
            }

            // Try comma-separated format "lat,lng"
            $parts = explode(',', $value);
            if (count($parts) === 2) {
                return new self((float) trim($parts[0]), (float) trim($parts[1]));
            }
        }

        throw new \InvalidArgumentException(
            'Coordinates::fromPrimitive() expects JSON string or array with latitude/longitude, got '.get_debug_type($value)
        );
    }

    /**
     * Get the SQL column type for migrations.
     *
     * @return non-empty-string
     */
    #[\Override]
    public static function columnType(): string
    {
        return 'json';
    }
}
