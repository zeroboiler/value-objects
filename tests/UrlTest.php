<?php

/**
 * This file is part of ZeroBoiler, licensed under the proprietary license.
 */

declare(strict_types=1);

use Illuminate\Validation\ValidationException;
use ZeroBoiler\ValueObjects\Url;

test('url can be created', function (): void {
    $url = new Url('https://example.com/path?query=value');

    expect($url->value)->toBe('https://example.com/path?query=value');
});

test('url is trimmed', function (): void {
    $url = new Url('  https://example.com  ');

    expect($url->value)->toBe('https://example.com');
});

test('invalid url throws validation exception', function (): void {
    expect(fn (): Url => new Url('not-a-url'))->toThrow(ValidationException::class);
});

test('url can extract scheme', function (): void {
    $url = new Url('https://example.com');

    expect($url->scheme())->toBe('https');
});

test('url can extract host', function (): void {
    $url = new Url('https://example.com/path');

    expect($url->host())->toBe('example.com');
});

test('url can extract path', function (): void {
    $url = new Url('https://example.com/path/to/resource');

    expect($url->path())->toBe('/path/to/resource');
});

test('url path defaults to slash', function (): void {
    $url = new Url('https://example.com');

    expect($url->path())->toBe('/');
});

test('url can extract query string', function (): void {
    $url = new Url('https://example.com?foo=bar&baz=qux');

    expect($url->query())->toBe('foo=bar&baz=qux');
});

test('url query defaults to empty string', function (): void {
    $url = new Url('https://example.com');

    expect($url->query())->toBe('');
});

test('url can extract fragment', function (): void {
    $url = new Url('https://example.com#section');

    expect($url->fragment())->toBe('section');
});

test('url fragment defaults to empty string', function (): void {
    $url = new Url('https://example.com');

    expect($url->fragment())->toBe('');
});

test('url can extract query parameters as array', function (): void {
    $url = new Url('https://example.com?foo=bar&baz=qux');

    $params = $url->queryParams();

    expect($params)->toBe([
        'foo' => 'bar',
        'baz' => 'qux',
    ]);
});

test('url query params defaults to empty array', function (): void {
    $url = new Url('https://example.com');

    expect($url->queryParams())->toBe([]);
});

test('url can check if https', function (): void {
    $httpsUrl = new Url('https://example.com');
    $httpUrl = new Url('http://example.com');

    expect($httpsUrl->isHttps())->toBeTrue()
        ->and($httpUrl->isHttps())->toBeFalse();
});

test('url can check if http', function (): void {
    $httpsUrl = new Url('https://example.com');
    $httpUrl = new Url('http://example.com');

    expect($httpsUrl->isHttp())->toBeFalse()
        ->and($httpUrl->isHttp())->toBeTrue();
});

test('url can be created with new scheme', function (): void {
    $url = new Url('http://example.com');

    $httpsUrl = $url->withScheme('https');

    expect($httpsUrl->scheme())->toBe('https')
        ->and($httpsUrl->isHttps())->toBeTrue();
});

// Bug fix tests: #488 — withScheme breaks for URLs with 'http' or 'https' in path

test('url withScheme preserves path containing http (#488)', function (): void {
    $url = new Url('https://example.com/http-redirect-target');
    $result = $url->withScheme('http');

    expect($result->value)->toBe('http://example.com/http-redirect-target');
});

test('url withScheme preserves path containing https (#488)', function (): void {
    $url = new Url('http://example.com/https-everywhere');
    $result = $url->withScheme('https');

    expect($result->value)->toBe('https://example.com/https-everywhere');
});

test('url withScheme preserves query and fragment', function (): void {
    $url = new Url('http://example.com/path?redirect=https://other.com#section');
    $result = $url->withScheme('https');

    expect($result->scheme())->toBe('https')
        ->and($result->host())->toBe('example.com')
        ->and($result->path())->toBe('/path')
        ->and($result->query())->toBe('redirect=https://other.com')
        ->and($result->fragment())->toBe('section');
});

test('url withScheme works with port and userinfo', function (): void {
    $url = new Url('http://user:pass@example.com:8080/path');
    $result = $url->withScheme('https');

    expect($result->scheme())->toBe('https')
        ->and($result->host())->toBe('example.com');
});

test('url equals compares by value', function (): void {
    $url1 = new Url('https://example.com');
    $url2 = new Url('https://example.com');
    $url3 = new Url('https://other.com');

    expect($url1->equals($url2))->toBeTrue()
        ->and($url1->equals($url3))->toBeFalse();
});

test('url can be converted to string', function (): void {
    $url = new Url('https://example.com');

    expect((string) $url)->toBe('https://example.com');
});

test('url can be serialized', function (): void {
    $url = new Url('https://example.com/path?foo=bar#section');

    expect($url->toArray())->toBe([
        'url' => 'https://example.com/path?foo=bar#section',
        'scheme' => 'https',
        'host' => 'example.com',
        'path' => '/path',
        'query' => 'foo=bar',
        'fragment' => 'section',
    ]);
});
