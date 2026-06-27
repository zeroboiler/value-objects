<?php

/**
 * This file is part of ZeroBoiler, licensed under the proprietary license.
 */

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects;

use Illuminate\Validation\ValidationException;

/**
 * Phone number value object in E.164 format.
 */
final class PhoneNumber extends ValueObject
{
    use Castable;

    /** E.164 formatted phone number (e.g., "+1234567890") */
    public string $value;

    /**
     * @param  string  $phoneNumber  Phone number in E.164 format
     *
     * @throws ValidationException If phone number is invalid
     */
    public function __construct(string $phoneNumber)
    {
        $normalized = trim($phoneNumber);

        $this->validate(
            ['phone' => $normalized],
            ['phone' => 'required|string|min:8|max:15|regex:/^\+[1-9]\d{1,14}$/']
        );

        $this->value = $normalized;
    }

    /**
     * Get country code from phone number.
     *
     * @return string Country code (e.g., "1", "44", "86")
     */
    public function countryCode(): string
    {
        $withoutPlus = ltrim($this->value, '+');

        // E.164 country codes are typically 1-3 digits
        // Try to extract meaningful country code based on the number format
        if (strlen($withoutPlus) === 11 && $withoutPlus[0] === '1') {
            return '1'; // US/Canada
        }

        if (strlen($withoutPlus) === 12 && $withoutPlus[0] === '4' && $withoutPlus[1] === '4') {
            return '44'; // UK
        }

        // Default: take 1-3 digits based on total length
        if (strlen($withoutPlus) >= 12) {
            return substr($withoutPlus, 0, 3);
        }

        if (strlen($withoutPlus) >= 11) {
            return substr($withoutPlus, 0, 2);
        }

        return substr($withoutPlus, 0, 1);
    }

    /**
     * Format for display (add spaces for readability).
     *
     * @return string Human-readable format (e.g., "+1 234 567 8900")
     */
    public function format(): string
    {
        $withoutPlus = ltrim($this->value, '+');
        $code = $this->countryCode();
        $number = substr($withoutPlus, strlen($code));

        // Format based on length
        if (strlen($number) === 10) {
            // US/Canada format: +1 234 567 8900
            $parts = str_split($number, 3);
            $parts[2] = substr($number, 6, 4);

            return "+{$code} {$parts[0]} {$parts[1]} {$parts[2]}";
        }

        if (strlen($number) <= 7) {
            // Short format: +44 123 4567
            $parts = str_split($number, 3);

            return "+{$code} {$parts[0]} ".implode('', array_slice($parts, 1));
        }

        // Long format: group by 2-3 digits from end
        $formatted = '';
        $count = 0;

        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            if ($count > 0 && $count % 3 === 0) {
                $formatted = ' '.$formatted;
            }

            $formatted = $number[$i].$formatted;
            $count++;
        }

        return "+{$code} {$formatted}";
    }

    public function toArray(): array
    {
        return ['phone' => $this->value];
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
