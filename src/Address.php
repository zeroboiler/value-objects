<?php

/**
 * This file is part of ZeroBoiler, licensed under the proprietary license.
 */

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects;

/**
 * Address value object.
 */
final class Address extends ValueObject
{
    use Castable;

    /** Street address (line 1) */
    public string $street;

    /** Optional apartment/suite number */
    public ?string $street2;

    /** City */
    public string $city;

    /** State/Province */
    public string $state;

    /** ZIP/Postal code */
    public string $postalCode;

    /** Country name or ISO code */
    public string $country;

    public function __construct(
        string $street,
        ?string $street2,
        string $city,
        string $state,
        string $postalCode,
        string $country
    ) {
        $this->validate(
            ['street' => $street, 'street2' => $street2, 'city' => $city, 'state' => $state, 'postalCode' => $postalCode, 'country' => $country],
            [
                'street' => 'required|string|max:255',
                'street2' => 'nullable|string|max:255',
                'city' => 'required|string|max:100',
                'state' => 'required|string|max:100',
                'postalCode' => 'required|string|max:20',
                'country' => 'required|string|max:100',
            ]
        );

        $this->street = trim($street);
        $this->street2 = $street2 !== null ? trim($street2) : null;
        $this->city = trim($city);
        $this->state = trim($state);
        $this->postalCode = trim($postalCode);
        $this->country = trim($country);
    }

    /**
     * Get full address as single line.
     *
     * @return string Formatted address
     */
    public function full(): string
    {
        $parts = array_filter([
            $this->street,
            $this->street2,
            $this->city,
            $this->state,
            $this->postalCode,
            $this->country,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get address as multiline array.
     *
     * @return array<int, string>
     */
    public function lines(): array
    {
        return array_filter([
            $this->street,
            $this->street2,
            trim("{$this->city}, {$this->state} {$this->postalCode}"),
            $this->country,
        ], fn (?string $line): bool => $line !== null && $line !== '');
    }

    public function toArray(): array
    {
        return [
            'street' => $this->street,
            'street2' => $this->street2,
            'city' => $this->city,
            'state' => $this->state,
            'postalCode' => $this->postalCode,
            'country' => $this->country,
        ];
    }

    public function __toString(): string
    {
        return $this->full();
    }
}
