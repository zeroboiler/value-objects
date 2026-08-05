<?php

/**
 * This file is part of ZeroBoiler, licensed under the proprietary license.
 */

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects;

use Illuminate\Container\Container;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Validation\ValidationException;

/**
 * URL value object with validation and parsing.
 *
 * Supports all standard URL schemes (http, https, ftp, mailto, ws, etc.)
 * rather than being limited to http/https only.
 */
final class Url extends ValueObject
{
    /** @use Castable<self> */
    use Castable;

    /** Full URL */
    public readonly string $value;

    /** Parsed URL components
     * @var array<string, string|int|null>
     */
    private array $parsed;

    /**
     * @param  string  $url  Valid URL
     *
     * @throws ValidationException If URL is invalid
     */
    public function __construct(string $url)
    {
        $normalized = trim($url);

        $this->validate(
            ['url' => $normalized],
            ['url' => 'required|url|max:2048']
        );

        $parsed = parse_url($normalized);

        if ($parsed === false) {
            $factory = Container::getInstance()->make(ValidationFactory::class);
            $factory->make(['url' => $normalized], ['url' => 'required|url'])->validate();
        }

        $this->value = $normalized;
        $this->parsed = $parsed;
    }

    /**
     * Get URL scheme (e.g., "https", "http", "ftp").
     */
    public function scheme(): string
    {
        return $this->parsed['scheme'] ?? '';
    }

    /**
     * Get URL host (e.g., "example.com").
     */
    public function host(): string
    {
        return $this->parsed['host'] ?? '';
    }

    /**
     * Get URL path (e.g., "/path/to/resource").
     */
    public function path(): string
    {
        return $this->parsed['path'] ?? '/';
    }

    /**
     * Get URL query string (e.g., "foo=bar&baz=qux").
     */
    public function query(): string
    {
        return $this->parsed['query'] ?? '';
    }

    /**
     * Get URL fragment (e.g., "section").
     */
    public function fragment(): string
    {
        return $this->parsed['fragment'] ?? '';
    }

    /**
     * Get query parameters as flat array.
     *
     * Note: `parse_str` can produce nested arrays for bracket-syntax params
     * like `foo[bar]=baz`. Only the top-level string values are returned.
     *
     * @return array<string, string>
     */
    public function queryParams(): array
    {
        if ($this->query() === '') {
            return [];
        }

        parse_str($this->query(), $params);

        // Flatten: keep only scalar string values for a consistent return type.
        return array_filter(
            $params,
            fn (mixed $v): bool => is_string($v),
        );
    }

    /**
     * Check if URL uses HTTPS.
     */
    public function isHttps(): bool
    {
        return strtolower($this->scheme()) === 'https';
    }

    /**
     * Check if URL uses HTTP.
     */
    public function isHttp(): bool
    {
        return strtolower($this->scheme()) === 'http';
    }

    /**
     * Get URL with modified scheme.
     *
     * Supports any valid URI scheme per RFC 3986 (http, https, ftp, mailto,
     * ws, wss, etc.). For schemes that use authority-less format (mailto:,
     * tel:, data:), the URL is reconstructed appropriately.
     *
     * @param  string  $scheme  New scheme (e.g., "https", "ftp", "mailto")
     *
     * @throws ValidationException If scheme is invalid
     */
    public function withScheme(string $scheme): self
    {
        $scheme = strtolower(trim($scheme));

        // Validate scheme format per RFC 3986: ALPHA *( ALPHA / DIGIT / "+" / "-" / "." )
        if (! preg_match('/^[a-z][a-z0-9+\\-.]*$/', $scheme)) {
            $validator = Container::getInstance()->make(ValidationFactory::class);
            $validator->make(
                ['scheme' => $scheme],
                ['scheme' => 'required|string|regex:/^[a-z][a-z0-9+\\-.]*$/']
            )->validate();
        }

        $parsed = $this->parsed;
        $parsed['scheme'] = $scheme;

        // For schemes without authority (mailto, tel, data, etc.),
        // construct the URL using scheme:path format without //.
        $authorityLessSchemes = ['mailto', 'tel', 'fax', 'sms', 'data', 'blob'];
        if (in_array($scheme, $authorityLessSchemes, true)) {
            $path = $parsed['path'] ?? '';
            $query = isset($parsed['query']) ? '?'.$parsed['query'] : '';
            $fragment = isset($parsed['fragment']) ? '#'.$parsed['fragment'] : '';

            return new self("{$scheme}:{$path}{$query}{$fragment}");
        }

        return new self($this->buildUrl($parsed));
    }

    /**
     * Reconstruct URL from parsed components.
     *
     * @param  array<string, string|int|null>  $parsed
     */
    private function buildUrl(array $parsed): string
    {
        $scheme = isset($parsed['scheme']) ? $parsed['scheme'].'://' : '';
        $host = $parsed['host'] ?? '';
        $port = isset($parsed['port']) ? ':'.$parsed['port'] : '';
        $user = $parsed['user'] ?? '';
        $pass = isset($parsed['pass']) ? ':'.$parsed['pass'] : '';
        $userinfo = $user !== '' ? $user.$pass.'@' : '';
        $path = $parsed['path'] ?? '';
        $query = isset($parsed['query']) ? '?'.$parsed['query'] : '';
        $fragment = isset($parsed['fragment']) ? '#'.$parsed['fragment'] : '';

        return $scheme.$userinfo.$host.$port.$path.$query.$fragment;
    }

    /**
     * Serialize to array for round-trip-safe storage.
     *
     * Returns only the constructor-shaped data so that
     * ValueObjectCast can reconstruct the VO unambiguously.
     *
     * Use {@see toExpandedArray()} for display/API output that
     * includes all parsed URL components.
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return ['url' => $this->value];
    }

    /**
     * Serialize to expanded array with all parsed URL components.
     *
     * Use this for API responses or display where the consumer
     * wants direct access to scheme, host, path, query, fragment
     * without re-parsing the URL.
     *
     * @return array<string, string>
     */
    public function toExpandedArray(): array
    {
        return [
            'url' => $this->value,
            'scheme' => $this->scheme(),
            'host' => $this->host(),
            'path' => $this->path(),
            'query' => $this->query(),
            'fragment' => $this->fragment(),
        ];
    }

    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Get the primitive value for database storage.
     */
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
            throw new \InvalidArgumentException('Url expects a string, got '.get_debug_type($value));
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
