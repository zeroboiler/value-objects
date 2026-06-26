<?php

use ZeroBoiler\ValueObjects\ValueObjects\Url;
use Illuminate\Validation\ValidationException;

test('url can be created', function () {
    $url = new Url('https://example.com/path?query=value');

    expect($url->value)->toBe('https://example.com/path?query=value');
});

test('url is trimmed', function () {
    $url = new Url('  https://example.com  ');

    expect($url->value)->toBe('https://example.com');
});

test('invalid url throws validation exception', function () {
    expect(fn () => new Url('not-a-url'))->toThrow(ValidationException::class);
});

test('url can extract scheme', function () {
    $url = new Url('https://example.com');

    expect($url->scheme())->toBe('https');
});

test('url can extract host', function () {
    $url = new Url('https://example.com/path');

    expect($url->host())->toBe('example.com');
});

test('url can extract path', function () {
    $url = new Url('https://example.com/path/to/resource');

    expect($url->path())->toBe('/path/to/resource');
});

test('url path defaults to slash', function () {
    $url = new Url('https://example.com');

    expect($url->path())->toBe('/');
});

test('url can extract query string', function () {
    $url = new Url('https://example.com?foo=bar&baz=qux');

    expect($url->query())->toBe('foo=bar&baz=qux');
});

test('url query defaults to empty string', function () {
    $url = new Url('https://example.com');

    expect($url->query())->toBe('');
});

test('url can extract fragment', function () {
    $url = new Url('https://example.com#section');

    expect($url->fragment())->toBe('section');
});

test('url fragment defaults to empty string', function () {
    $url = new Url('https://example.com');

    expect($url->fragment())->toBe('');
});

test('url can extract query parameters as array', function () {
    $url = new Url('https://example.com?foo=bar&baz=qux');

    $params = $url->queryParams();

    expect($params)->toBe([
        'foo' => 'bar',
        'baz' => 'qux',
    ]);
});

test('url query params defaults to empty array', function () {
    $url = new Url('https://example.com');

    expect($url->queryParams())->toBe([]);
});

test('url can check if https', function () {
    $httpsUrl = new Url('https://example.com');
    $httpUrl = new Url('http://example.com');

    expect($httpsUrl->isHttps())->toBeTrue()
        ->and($httpUrl->isHttps())->toBeFalse();
});

test('url can check if http', function () {
    $httpsUrl = new Url('https://example.com');
    $httpUrl = new Url('http://example.com');

    expect($httpsUrl->isHttp())->toBeFalse()
        ->and($httpUrl->isHttp())->toBeTrue();
});

test('url can be created with new scheme', function () {
    $url = new Url('http://example.com');

    $httpsUrl = $url->withScheme('https');

    expect($httpsUrl->scheme())->toBe('https')
        ->and($httpsUrl->isHttps())->toBeTrue();
});

test('url equals compares by value', function () {
    $url1 = new Url('https://example.com');
    $url2 = new Url('https://example.com');
    $url3 = new Url('https://other.com');

    expect($url1->equals($url2))->toBeTrue()
        ->and($url1->equals($url3))->toBeFalse();
});

test('url can be converted to string', function () {
    $url = new Url('https://example.com');

    expect((string) $url)->toBe('https://example.com');
});

test('url can be serialized', function () {
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