<?php

/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects;

use Illuminate\Validation\ValidationException;

/**
 * Phone number value object in E.164 format.
 *
 * Country code detection uses a prefix-based lookup against the ITU-T E.164
 * country calling code table. This is a deterministic heuristic — it correctly
 * identifies all assigned country codes but cannot disambiguate countries that
 * share a code (e.g., US/Canada/Caribbean all use +1). For full phone number
 * parsing including region detection, install giggsey/libphonenumber-for-php
 * (suggested in composer.json) and use it alongside this value object.
 */
final class PhoneNumber extends ValueObject
{
    /** @use Castable<self> */
    use Castable;

    /** E.164 formatted phone number (e.g., "+1234567890") */
    public readonly string $value;

    /**
     * @param  string  $phoneNumber  Phone number in E.164 format
     *
     * @throws ValidationException If phone number is invalid
     */
    public function __construct(string $phoneNumber): void
    {
        $normalized = trim($phoneNumber);

        $this->validate(
            ['phone' => $normalized],
            ['phone' => 'required|string|min:8|max:15|regex:/^\+[1-9]\d{1,14}$/']
        );

        $this->value = $normalized;
    }

    /**
     * List of country calling codes (sorted by length for correct matching).
     * Source: ITU-T E.164 recommended country codes.
     *
     * @var array<int, string>
     */
    private const array COUNTRY_CODES = [
        '1', '7',
        '20', '27', '30', '31', '32', '33', '34', '36', '39', '40', '41', '43',
        '44', '45', '46', '47', '48', '49', '51', '52', '53', '54', '55', '56',
        '57', '58', '60', '61', '62', '63', '64', '65', '66', '81', '82', '84',
        '86', '90', '91', '92', '93', '94', '95', '98',
        '210', '211', '212', '213', '214', '215', '216', '218', '220', '221',
        '222', '223', '224', '225', '226', '227', '228', '229', '230', '231',
        '232', '233', '234', '235', '236', '237', '238', '239', '240', '241',
        '242', '243', '244', '245', '246', '248', '249', '250', '251', '252',
        '253', '254', '255', '256', '257', '258', '260', '261', '262', '263',
        '264', '265', '266', '267', '268', '269', '290', '291', '297', '298',
        '299', '350', '351', '352', '353', '354', '355', '356', '357', '358',
        '359', '370', '371', '372', '373', '374', '375', '376', '377', '378',
        '380', '381', '382', '383', '385', '386', '387', '389', '420', '421',
        '423', '500', '501', '502', '503', '504', '505', '506', '507', '508',
        '509', '590', '591', '592', '593', '594', '595', '596', '597', '598',
        '599', '670', '672', '673', '674', '675', '676', '677', '678', '679',
        '680', '681', '682', '683', '685', '686', '687', '688', '689', '691',
        '692', '850', '852', '853', '855', '856', '870', '878', '880', '881',
        '882', '883', '886', '888', '960', '961', '962', '963', '964', '965',
        '966', '967', '968', '970', '971', '972', '973', '974', '975', '976',
        '977', '992', '993', '994', '995', '996', '998',
    ];

    /**
     * Get country calling code from phone number.
     *
     * Uses longest-prefix matching against the full ITU-T E.164 country calling
     * code table. This reliably extracts the dialling code (e.g., "1", "44",
     * "90", "351") but cannot determine the specific country when multiple
     * countries share a code (e.g., +1 covers US, Canada, and NANP Caribbean).
     *
     * For country-level resolution, use giggsey/libphonenumber-for-php.
     *
     * @return string Country calling code (e.g., "1", "44", "90", "351")
     */
    public function countryCode(): string
    {
        $withoutPlus = ltrim($this->value, '+');

        // Try 3-digit codes first, then 2-digit, then 1-digit
        // This ensures we match the longest possible prefix (most specific)
        for ($length = 3; $length >= 1; $length--) {
            $prefix = substr($withoutPlus, 0, $length);

            if (in_array($prefix, self::COUNTRY_CODES, true)) {
                return $prefix;
            }
        }

        // Fallback: return first digit (should not happen for valid E.164)
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

        // US/Canada NANP format: +1 NXX NXX XXXX
        if ($code === '1' && strlen($number) === 10) {
            return sprintf('+%s %s %s %s',
                $code,
                substr($number, 0, 3),
                substr($number, 3, 3),
                substr($number, 6, 4)
            );
        }

        // UK format: +44 20 1234 5678 or +44 7123 456789
        // UK area codes vary: 2-digit (e.g., 20 for London), 3-digit (e.g., 121 for Birmingham),
        // 4-digit (e.g., 1263 for smaller areas), or mobile (7xxx, 8xxx, 9xxx).
        if ($code === '44') {
            // UK numbers after the '44' country code are 9-10 digits.
            // Area codes starting with 1 or 2 are geographic (2-4 digits area code).
            // Numbers starting with 7 are mobile (+44 7XXX XXXXXX).
            if (strlen($number) >= 9 && strlen($number) <= 10) {
                // Determine area code length based on UK numbering rules
                $firstDigit = $number[0];

                if ($firstDigit === '1' || $firstDigit === '2') {
                    // Geographic: 2-digit area code for large cities (e.g., 20 London, 121 Birmingham)
                    // Use 3 for 10-digit numbers, 2 for 9-digit numbers
                    $areaLen = strlen($number) === 10 ? 3 : 2;
                } elseif ($firstDigit === '7') {
                    // Mobile numbers: 4-digit prefix (e.g., 7123)
                    $areaLen = 4;
                } else {
                    // Other UK numbers: default to 3-digit grouping
                    $areaLen = 3;
                }

                $areaCode = substr($number, 0, $areaLen);
                $subscriber = substr($number, $areaLen);

                // Format subscriber number in groups of 4 for readability
                $formattedSubscriber = trim(chunk_split($subscriber, 4, ' '));

                return sprintf('+%s %s %s', $code, $areaCode, $formattedSubscriber);
            }
        }

        // Generic: group subscriber number in blocks of 3-4
        $formatted = trim(chunk_split($number, 3, ' '));

        return "+{$code} {$formatted}";
    }

    #[\Override]
    public function toArray(): array
    {
        return ['phone' => $this->value];
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Get the primitive value for database storage.
     */
    #[\Override]
    #[\Override]
    public function toPrimitive(): mixed
    {
        return $this->value;
    }

    /**
     * Create from a primitive database value (string).
     */
    #[\Override]
    public static function fromPrimitive(mixed $value): static
    {
        if (! is_string($value)) {
            throw new \InvalidArgumentException('PhoneNumber expects a string, got '.get_debug_type($value));
        }

        return new self($value);
    }

    /**
     * Get the SQL column type for migrations.
     *
     * @return non-empty-string
     */
    #[\Override]
    public static function columnType(): string
    {
        return 'string';
    }
}
