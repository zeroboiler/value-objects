<?php

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects;

use ValueError;

/**
 * Geographic coordinates value object (latitude and longitude).
 */
final class Coordinates extends ValueObject
{
    use Castable;

    /** Latitude in decimal degrees (-90 to 90) */
    public float $latitude;

    /** Longitude in decimal degrees (-180 to 180) */
    public float $longitude;

    /**
     * @param  float  $latitude  Latitude (-90 to 90)
     * @param  float  $longitude  Longitude (-180 to 180)
     * @throws ValueError If coordinates are invalid
     */
    public function __construct(float $latitude, float $longitude)
    {
        if (!$this->isValidLat($latitude)) {
            throw new ValueError("Invalid latitude: {$latitude}. Must be between -90 and 90.");
        }

        if (!$this->isValidLng($longitude)) {
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
}