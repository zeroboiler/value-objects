<?php

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects\Tests;

use ZeroBoiler\ValueObjects\ValueObject;

/**
 * Dummy value object for testing.
 */
final class DummyValueObject extends ValueObject
{
    public string $value;

    public int $count;

    public function __construct(string $value, int $count)
    {
        $this->validate(
            ['value' => $value, 'count' => $count],
            [
                'value' => 'required|string|min:3',
                'count' => 'required|integer|min:1|max:100',
            ]
        );

        $this->value = $value;
        $this->count = $count;
    }

    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'count' => $this->count,
        ];
    }

    public function __toString(): string
    {
        return "{$this->value}-{$this->count}";
    }
}