<?php
/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

use ZeroBoiler\ValueObjects\Coordinates;

test('coordinates can be created', function (): void {
    $coords = new Coordinates(40.7128, -74.0060);

    expect($coords->latitude)->toBe(40.7128)
        ->and($coords->longitude)->toBe(-74.0060);
});

test('coordinates must have valid latitude', function (): void {
    expect(fn (): Coordinates => new Coordinates(91.0, 0.0))->toThrow(ValueError::class);
    expect(fn (): Coordinates => new Coordinates(-91.0, 0.0))->toThrow(ValueError::class);
});

test('coordinates must have valid longitude', function (): void {
    expect(fn (): Coordinates => new Coordinates(0.0, 181.0))->toThrow(ValueError::class);
    expect(fn (): Coordinates => new Coordinates(0.0, -181.0))->toThrow(ValueError::class);
});

test('coordinates can check valid latitude', function (): void {
    $coords = new Coordinates(40.7128, -74.0060);

    expect($coords->isValidLat(40.7128))->toBeTrue()
        ->and($coords->isValidLat(91.0))->toBeFalse();
});

test('coordinates can check valid longitude', function (): void {
    $coords = new Coordinates(40.7128, -74.0060);

    expect($coords->isValidLng(-74.0060))->toBeTrue()
        ->and($coords->isValidLng(181.0))->toBeFalse();
});

test('coordinates can calculate distance to another point', function (): void {
    $coords1 = new Coordinates(40.7128, -74.0060); // New York
    $coords2 = new Coordinates(34.0522, -118.2437); // Los Angeles

    $distance = $coords1->distanceTo($coords2);

    expect($distance)->toBeGreaterThan(0)
        ->and($distance)->toBeLessThan(5000000); // Less than 5000 km in meters
});

test('coordinates distance to same point is zero', function (): void {
    $coords = new Coordinates(40.7128, -74.0060);

    expect($coords->distanceTo($coords))->toBe(0.0);
});

test('coordinates can calculate distance in kilometers', function (): void {
    $coords1 = new Coordinates(40.7128, -74.0060);
    $coords2 = new Coordinates(34.0522, -118.2437);

    $distanceKm = $coords1->distanceToKm($coords2);

    expect($distanceKm)->toBeGreaterThan(0)
        ->and($distanceKm)->toBeLessThan(5000); // Less than 5000 km
});

test('coordinates can calculate distance in miles', function (): void {
    $coords1 = new Coordinates(40.7128, -74.0060);
    $coords2 = new Coordinates(34.0522, -118.2437);

    $distanceMiles = $coords1->distanceToMiles($coords2);

    expect($distanceMiles)->toBeGreaterThan(0)
        ->and($distanceMiles)->toBeLessThan(3100); // Less than 3100 miles
});

test('coordinates equals compares by value', function (): void {
    $c1 = new Coordinates(40.7128, -74.0060);
    $c2 = new Coordinates(40.7128, -74.0060);
    $c3 = new Coordinates(34.0522, -118.2437);

    expect($c1->equals($c2))->toBeTrue()
        ->and($c1->equals($c3))->toBeFalse();
});

test('coordinates can be converted to string', function (): void {
    $coords = new Coordinates(40.7128, -74.0060);

    expect((string) $coords)->toBe('(40.7128, -74.006)');
});

test('coordinates can be serialized', function (): void {
    $coords = new Coordinates(40.7128, -74.0060);

    expect($coords->toArray())->toBe([
        'latitude' => 40.7128,
        'longitude' => -74.006,
    ]);
});
