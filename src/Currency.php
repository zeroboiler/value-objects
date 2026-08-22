<?php
/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects;

use NumberFormatter;
use ValueError;
use ZeroBoiler\ValueObjects\Contracts\ValueObject as ValueObjectContract;

/**
 * Currency value object representing an ISO 4217 currency.
 *
 * Encapsulates the currency code together with its metadata:
 * the number of decimal places and the subunit divisor.
 *
 * @since 1.0.0
 */
final class Currency extends ValueObject
{
    /** @use Castable<self> */
    use Castable;

    /** ISO 4217 currency code (e.g., "USD", "EUR", "JPY") */
    public readonly string $code;

    /**
     * Currencies with zero decimal places (no "cents" subunit).
     * Source: ISO 4217.
     *
     * @var array<int, string>
     */
    private const array ZERO_DECIMAL_CURRENCIES = [
        'BIF', 'CLP', 'DJF', 'GNF', 'ISK', 'JPY', 'KMF', 'KRW', 'MGA',
        'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF',
    ];

    /**
     * Currencies with 3 decimal places.
     *
     * @var array<int, string>
     */
    private const array THREE_DECIMAL_CURRENCIES = [
        'BHD', 'IQD', 'JOD', 'KWD', 'LYD', 'OMR', 'TND',
    ];

    /**
     * All active ISO 4217 currency codes.
     *
     * Source: ISO 4217 — current active codes.
     *
     * @var array<int, string>
     */
    private const array ISO_4217_CODES = [
        'AED', 'AFN', 'ALL', 'AMD', 'ANG', 'AOA', 'ARS', 'AUD', 'AWG', 'AZN',
        'BAM', 'BBD', 'BDT', 'BGN', 'BHD', 'BIF', 'BMD', 'BND', 'BOB', 'BOV',
        'BRL', 'BSD', 'BTN', 'BWP', 'BYN', 'BZD', 'CAD', 'CDF', 'CHE', 'CHF',
        'CHW', 'CLF', 'CLP', 'CNY', 'COP', 'COU', 'CRC', 'CUP', 'CVE', 'CZK',
        'DJF', 'DKK', 'DOP', 'DZD', 'EGP', 'ERN', 'ETB', 'EUR', 'FJD', 'FKP',
        'GBP', 'GEL', 'GHS', 'GIP', 'GMD', 'GNF', 'GTQ', 'GYD', 'HKD', 'HNL',
        'HRK', 'HTG', 'HUF', 'IDR', 'ILS', 'INR', 'IQD', 'IRR', 'ISK', 'JMD',
        'JOD', 'JPY', 'KES', 'KGS', 'KHR', 'KMF', 'KPW', 'KRW', 'KWD', 'KYD',
        'KZT', 'LAK', 'LBP', 'LKR', 'LRD', 'LSL', 'LYD', 'MAD', 'MDL', 'MGA',
        'MKD', 'MMK', 'MNT', 'MOP', 'MRU', 'MUR', 'MVR', 'MWK', 'MXN', 'MXV',
        'MYR', 'MZN', 'NAD', 'NGN', 'NIO', 'NOK', 'NPR', 'NZD', 'OMR', 'PAB',
        'PEN', 'PGK', 'PHP', 'PKR', 'PLN', 'PYG', 'QAR', 'RON', 'RSD', 'RUB',
        'RWF', 'SAR', 'SBD', 'SCR', 'SDG', 'SEK', 'SGD', 'SHP', 'SLL', 'SOS',
        'SRD', 'SSP', 'STN', 'SVC', 'SYP', 'SZL', 'THB', 'TJS', 'TMT', 'TND',
        'TOP', 'TRY', 'TTD', 'TWD', 'TZS', 'UAH', 'UGX', 'USD', 'USN', 'UYI',
        'UYU', 'UZS', 'VED', 'VES', 'VND', 'VUV', 'WST', 'XAF', 'XCD', 'XOF',
        'XPF', 'YER', 'ZAR', 'ZMW', 'ZWL',
    ];

    /**
     * @since 1.0.0
     *
     * @param  string  $code  ISO 4217 currency code (case-insensitive)
     *
     * @throws ValueError If the currency code is not a valid ISO 4217 code
     */
    public function __construct(string $code): void
    {
        $code = strtoupper(trim($code));

        if (! $this->isValidCode($code)) {
            throw new ValueError("Invalid ISO 4217 currency code: '{$code}'");
        }

        $this->validate(
            ['code' => $code],
            ['code' => 'required|string|size:3'],
        );

        $this->code = $code;
    }

    /**
     * Create from a 3-letter ISO 4217 code.
     *
     * Convenience named constructor for readability.
     *
     * @since 1.0.0
     */
    public static function fromCode(string $code): self
    {
        return new self($code);
    }

    /**
     * Get the number of decimal places for this currency.
     *
     * Most currencies use 2 (e.g., USD: 100 cents = 1 dollar).
     * JPY, KRW, etc. use 0 (no subunit).
     * BHD, KWD, etc. use 3.
     *
     * @since 1.0.0
     */
    public function decimalPlaces(): int
    {
        if (in_array($this->code, self::ZERO_DECIMAL_CURRENCIES, true)) {
            return 0;
        }

        if (in_array($this->code, self::THREE_DECIMAL_CURRENCIES, true)) {
            return 3;
        }

        return 2;
    }

    /**
     * Get the subunit divisor (e.g., 100 for USD, 1 for JPY, 1000 for KWD).
     *
     * @since 1.0.0
     */
    public function subunitDivisor(): int
    {
        return 10 ** $this->decimalPlaces();
    }

    /**
     * Get the name of the subunit (e.g., "cent", "sen", "fils").
     *
     * @since 1.0.0
     */
    public function subunitName(): string
    {
        return match ($this->code) {
            'USD' => 'cent',
            'EUR' => 'cent',
            'GBP' => 'pence',
            'JPY' => 'sen',
            'KRW' => 'jeon',
            'BHD' => 'fils',
            'KWD' => 'fils',
            'JOD' => 'fils',
            'INR' => 'paise',
            default => 'unit',
        };
    }

    /**
     * Get the currency symbol (e.g., "$", "€", "¥").
     *
     * Uses PHP's NumberFormatter with the given locale (defaults to en_US).
     * Falls back to the ISO code itself if the symbol cannot be extracted.
     *
     * @since 1.0.0
     *
     * @param  string|null  $locale  Locale for formatting (e.g., "en_US", "tr_TR")
     * @return string Currency symbol (e.g., "$", "€", "¥") or ISO code as fallback
     */
    public function symbol(?string $locale = null): string
    {
        $locale ??= 'en_US';

        $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
        $formatter->setTextAttribute(NumberFormatter::CURRENCY_CODE, $this->code);
        $formatted = $formatter->formatCurrency(0, $this->code);

        // Extract the symbol prefix (everything before the first digit)
        $symbol = preg_replace('/[\d\s,.]+.*$/', '', $formatted);

        return trim((string) $symbol) !== '' ? trim((string) $symbol) : $this->code;
    }

    /**
     * Check if this currency equals another.
     *
     * @since 1.0.0
     */
    public function equals(?ValueObjectContract $other): bool
    {
        return $other instanceof self && $this->code === $other->code;
    }

    /**
     * Check if a code is a valid ISO 4217 currency code.
     */
    private function isValidCode(string $code): bool
    {
        return in_array($code, self::ISO_4217_CODES, true);
    }

    /**
     * Get all valid ISO 4217 currency codes.
     *
     * @since 1.0.0
     *
     * @return array<int, string>
     */
    public static function validCodes(): array
    {
        return self::ISO_4217_CODES;
    }

    /**
     * Check if a code is a valid ISO 4217 currency code.
     *
     * @since 1.0.0
     */
    public static function isValid(string $code): bool
    {
        return in_array(strtoupper(trim($code)), self::ISO_4217_CODES, true);
    }

    /**
     * @since 1.0.0
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'decimal_places' => $this->decimalPlaces(),
        ];
    }

    /**
     * @since 1.0.0
     */
    public function __toString(): string
    {
        return $this->code;
    }

    /**
     * Get the primitive value for database storage.
     *
     * @since 1.0.0
     *
     * @return mixed The ISO 4217 currency code as a string
     */
    public function toPrimitive(): mixed
    {
        return $this->code;
    }

    /**
     * Create from a primitive database value (string code).
     *
     * @since 1.0.0
     */
    public static function fromPrimitive(mixed $value): static
    {
        if (! is_string($value)) {
            throw new \ZeroBoiler\ValueObjects\Exceptions\InvalidValueObjectsArgumentException('Currency expects a string, got '.get_debug_type($value));
        }

        return new self($value);
    }

    /**
     * Get the SQL column type for migrations.
     *
     * @since 1.0.0
     *
     * @return non-empty-string
     */
    public static function columnType(): string
    {
        return 'string';
    }
}
