<?php

/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

/**
 * Tests for Duration new features: fromDays(), toDays(), fromHuman().
 */

use ZeroBoiler\ValueObjects\Duration;

describe('Duration::fromDays()', function (): void {
    it('creates duration from integer days', function (): void {
        $duration = Duration::fromDays(1);
        expect($duration->milliseconds)->toBe(86_400_000);
    });

    it('creates duration from multiple days', function (): void {
        $duration = Duration::fromDays(7);
        expect($duration->milliseconds)->toBe(604_800_000);
    });

    it('creates duration from fractional days', function (): void {
        $duration = Duration::fromDays(1.5);
        expect($duration->milliseconds)->toBe(129_600_000);
    });

    it('creates duration from zero days', function (): void {
        $duration = Duration::fromDays(0);
        expect($duration->milliseconds)->toBe(0);
    });
});

describe('Duration::toDays()', function (): void {
    it('converts one day to 1.0', function (): void {
        $duration = Duration::fromDays(1);
        expect($duration->toDays())->toBe(1.0);
    });

    it('converts hours to fractional days', function (): void {
        $duration = Duration::fromHours(12);
        expect($duration->toDays())->toBe(0.5);
    });

    it('converts minutes to fractional days', function (): void {
        $duration = Duration::fromMinutes(720); // 12 hours
        expect($duration->toDays())->toBe(0.5);
    });

    it('converts zero duration to 0.0 days', function (): void {
        $duration = new Duration(0);
        expect($duration->toDays())->toBe(0.0);
    });
});

describe('Duration::fromHuman()', function (): void {
    it('parses simple hours', function (): void {
        $duration = Duration::fromHuman('1h');
        expect($duration->milliseconds)->toBe(3_600_000);
    });

    it('parses simple minutes', function (): void {
        $duration = Duration::fromHuman('30m');
        expect($duration->milliseconds)->toBe(1_800_000);
    });

    it('parses simple seconds', function (): void {
        $duration = Duration::fromHuman('90s');
        expect($duration->milliseconds)->toBe(90_000);
    });

    it('parses milliseconds', function (): void {
        $duration = Duration::fromHuman('500ms');
        expect($duration->milliseconds)->toBe(500);
    });

    it('parses days', function (): void {
        $duration = Duration::fromHuman('2d');
        expect($duration->milliseconds)->toBe(172_800_000);
    });

    it('parses combined units', function (): void {
        $duration = Duration::fromHuman('1h 30m');
        expect($duration->milliseconds)->toBe(5_400_000);
    });

    it('parses all units combined', function (): void {
        $duration = Duration::fromHuman('1d 2h 30m 45s 500ms');
        // 1 day = 86400000
        // 2 hours = 7200000
        // 30 min = 1800000
        // 45 sec = 45000
        // 500 ms = 500
        expect($duration->milliseconds)->toBe(95_445_500);
    });

    it('parses without spaces', function (): void {
        $duration = Duration::fromHuman('1h30m');
        expect($duration->milliseconds)->toBe(5_400_000);
    });

    it('parses case-insensitively', function (): void {
        $duration = Duration::fromHuman('1H 30M');
        expect($duration->milliseconds)->toBe(5_400_000);
    });

    it('parses negative duration', function (): void {
        $duration = Duration::fromHuman('-1h 30m');
        expect($duration->milliseconds)->toBe(-5_400_000);
    });

    it('parses single millisecond', function (): void {
        $duration = Duration::fromHuman('1ms');
        expect($duration->milliseconds)->toBe(1);
    });

    it('parses large values', function (): void {
        $duration = Duration::fromHuman('365d');
        expect($duration->milliseconds)->toBe(31_536_000_000);
    });

    it('throws on empty string', function (): void {
        expect(fn (): Duration => Duration::fromHuman(''))
            ->toThrow(InvalidArgumentException::class);
    });

    it('throws on whitespace-only string', function (): void {
        expect(fn (): Duration => Duration::fromHuman('   '))
            ->toThrow(InvalidArgumentException::class);
    });

    it('throws on invalid format', function (): void {
        expect(fn (): Duration => Duration::fromHuman('abc'))
            ->toThrow(InvalidArgumentException::class);
    });

    it('throws on unrecognized units', function (): void {
        expect(fn (): Duration => Duration::fromHuman('1x 2y'))
            ->toThrow(InvalidArgumentException::class);
    });

    it('throws on partial unrecognized input', function (): void {
        expect(fn (): Duration => Duration::fromHuman('1h abc'))
            ->toThrow(InvalidArgumentException::class);
    });

    it('throws on numbers without units', function (): void {
        expect(fn (): Duration => Duration::fromHuman('123'))
            ->toThrow(InvalidArgumentException::class);
    });
});

describe('Duration humanReadable with days', function (): void {
    it('shows days for durations >= 1 day', function (): void {
        $duration = Duration::fromDays(1);
        expect($duration->humanReadable())->toBe('1 day');
    });

    it('shows plural days', function (): void {
        $duration = Duration::fromDays(3);
        expect($duration->humanReadable())->toBe('3 days');
    });

    it('shows days and hours together', function (): void {
        $duration = Duration::fromDays(1)->add(Duration::fromHours(2));
        expect($duration->humanReadable())->toBe('1 day 2 hours');
    });

    it('shows full complex duration', function (): void {
        $duration = Duration::fromDays(2)
            ->add(Duration::fromHours(3))
            ->add(Duration::fromMinutes(30))
            ->add(Duration::fromSeconds(15));

        expect($duration->humanReadable())->toBe('2 days 3 hours 30 minutes 15 seconds');
    });

    it('does not show days for sub-day durations', function (): void {
        $duration = Duration::fromHours(23);
        expect($duration->humanReadable())->toBe('23 hours');
    });
});
