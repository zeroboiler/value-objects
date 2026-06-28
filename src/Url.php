<?php

/**
 * This file is part of ZeroBoiler, licensed under the proprietary license.
 */

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * URL value object with validation and parsing.
 *
 * @extends ValueObject<array<string, mixed>>
 */
final class Url extends ValueObject
{
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
            throw new ValidationException(
                Validator::make(['url' => $normalized], ['url' => 'required|url'])
            );
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
     * Get query parameters as array.
     *
     * @return array<string, string>
     */
    public function queryParams(): array
    {
        if ($this->query() === '') {
            return [];
        }

        parse_str($this->query(), $params);

        return $params;
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
     * @param  string  $scheme  New scheme (e.g., "https")
     */
    public function withScheme(string $scheme): self
    {
        $parsed = $this->parsed;
        $parsed['scheme'] = strtolower($scheme);

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

    public function toArray(): array
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
}
