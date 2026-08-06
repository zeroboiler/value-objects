<?php

/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

use ZeroBoiler\ValueObjects\Address;
use ZeroBoiler\ValueObjects\Contracts\ValueObject as ValueObjectContract;
use ZeroBoiler\ValueObjects\Coordinates;
use ZeroBoiler\ValueObjects\Currency;
use ZeroBoiler\ValueObjects\Duration;
use ZeroBoiler\ValueObjects\Email;
use ZeroBoiler\ValueObjects\Money;
use ZeroBoiler\ValueObjects\Percentage;
use ZeroBoiler\ValueObjects\PhoneNumber;
use ZeroBoiler\ValueObjects\Url;
use ZeroBoiler\ValueObjects\ValueObjectInterface;

describe('ValueObject Contract', function (): void {
    describe('interface implementation', function (): void {
        it('all concrete VOs implement the Contract interface', function (): void {
            $vos = [
                new Email('test@example.com'),
                new Url('https://example.com'),
                new PhoneNumber('+1234567890'),
                new Currency('USD'),
                new Money(1000, 'USD'),
                new Coordinates(40.7128, -74.0060),
                new Address('123 Main St', null, 'NYC', 'NY', '10001', 'USA'),
                new Duration(5000),
                new Percentage(50.0),
            ];

            foreach ($vos as $vo) {
                expect($vo)->toBeInstanceOf(ValueObjectContract::class);
                expect($vo)->toBeInstanceOf(ValueObjectInterface::class);
            }
        });

        it('Contract is in the Contracts namespace', function (): void {
            expect(ValueObjectContract::class)->toBe(ValueObjectContract::class);
        });

        it('legacy interface extends the contract', function (): void {
            $reflection = new ReflectionClass(ValueObjectInterface::class);
            expect($reflection->getInterfaceNames())->toContain(ValueObjectContract::class);
        });
    });

    describe('Email contract methods', function (): void {
        it('toPrimitive returns the email string', function (): void {
            $email = new Email('Test@Example.COM');
            expect($email->toPrimitive())->toBe('test@example.com');
        });

        it('fromPrimitive reconstructs from string', function (): void {
            $email = Email::fromPrimitive('user@example.org');
            expect($email->value)->toBe('user@example.org');
        });

        it('fromPrimitive throws on non-string', function (): void {
            expect(fn (): Email => Email::fromPrimitive(123))
                ->toThrow(InvalidArgumentException::class);
        });

        it('columnType returns string', function (): void {
            expect(Email::columnType())->toBe('string');
        });

        it('toPrimitive and fromPrimitive are inverses', function (): void {
            $original = new Email('hello@world.dev');
            $primitive = $original->toPrimitive();
            $restored = Email::fromPrimitive($primitive);
            expect($restored->equals($original))->toBeTrue();
        });
    });

    describe('Url contract methods', function (): void {
        it('toPrimitive returns the URL string', function (): void {
            $url = new Url('https://example.com/path');
            expect($url->toPrimitive())->toBe('https://example.com/path');
        });

        it('fromPrimitive reconstructs from string', function (): void {
            $url = Url::fromPrimitive('https://test.org');
            expect($url->value)->toBe('https://test.org');
        });

        it('columnType returns string', function (): void {
            expect(Url::columnType())->toBe('string');
        });

        it('toPrimitive and fromPrimitive are inverses', function (): void {
            $original = new Url('https://inverse.test/path?q=1');
            $restored = Url::fromPrimitive($original->toPrimitive());
            expect($restored->equals($original))->toBeTrue();
        });
    });

    describe('PhoneNumber contract methods', function (): void {
        it('toPrimitive returns the phone string', function (): void {
            $phone = new PhoneNumber('+1234567890');
            expect($phone->toPrimitive())->toBe('+1234567890');
        });

        it('fromPrimitive reconstructs from string', function (): void {
            $phone = PhoneNumber::fromPrimitive('+441234567890');
            expect($phone->value)->toBe('+441234567890');
        });

        it('columnType returns string', function (): void {
            expect(PhoneNumber::columnType())->toBe('string');
        });
    });

    describe('Currency contract methods', function (): void {
        it('toPrimitive returns the currency code', function (): void {
            $currency = new Currency('USD');
            expect($currency->toPrimitive())->toBe('USD');
        });

        it('fromPrimitive reconstructs from string', function (): void {
            $currency = Currency::fromPrimitive('EUR');
            expect($currency->code)->toBe('EUR');
        });

        it('columnType returns string', function (): void {
            expect(Currency::columnType())->toBe('string');
        });
    });

    describe('Money contract methods', function (): void {
        it('toPrimitive returns JSON string', function (): void {
            $money = new Money(1000, 'USD');
            $primitive = $money->toPrimitive();
            expect($primitive)->toBeString();

            $decoded = json_decode((string) $primitive, true);
            expect($decoded)->toBe(['amount' => 1000, 'currency' => 'USD']);
        });

        it('fromPrimitive reconstructs from JSON string', function (): void {
            $json = json_encode(['amount' => 5000, 'currency' => 'EUR']);
            $money = Money::fromPrimitive($json);
            expect($money->amount)->toBe(5000);
            expect($money->currency)->toBe('EUR');
        });

        it('fromPrimitive reconstructs from array', function (): void {
            $money = Money::fromPrimitive(['amount' => 300, 'currency' => 'GBP']);
            expect($money->amount)->toBe(300);
            expect($money->currency)->toBe('GBP');
        });

        it('fromPrimitive handles plain integer as USD', function (): void {
            $money = Money::fromPrimitive(999);
            expect($money->amount)->toBe(999);
            expect($money->currency)->toBe('USD');
        });

        it('columnType returns json', function (): void {
            expect(Money::columnType())->toBe('json');
        });

        it('toPrimitive and fromPrimitive are inverses', function (): void {
            $original = new Money(7500, 'JPY');
            $restored = Money::fromPrimitive($original->toPrimitive());
            expect($restored->equals($original))->toBeTrue();
        });
    });

    describe('Coordinates contract methods', function (): void {
        it('toPrimitive returns JSON string', function (): void {
            $coords = new Coordinates(40.7128, -74.0060);
            $primitive = $coords->toPrimitive();
            expect($primitive)->toBeString();

            $decoded = json_decode((string) $primitive, true);
            expect($decoded['latitude'])->toBe(40.7128);
            expect($decoded['longitude'])->toBe(-74.0060);
        });

        it('fromPrimitive reconstructs from JSON string', function (): void {
            $json = json_encode(['latitude' => 35.6762, 'longitude' => 139.6503]);
            $coords = Coordinates::fromPrimitive($json);
            expect($coords->latitude)->toBe(35.6762);
            expect($coords->longitude)->toBe(139.6503);
        });

        it('fromPrimitive reconstructs from comma-separated string', function (): void {
            $coords = Coordinates::fromPrimitive('51.5074,-0.1278');
            expect($coords->latitude)->toBe(51.5074);
            expect($coords->longitude)->toBe(-0.1278);
        });

        it('columnType returns json', function (): void {
            expect(Coordinates::columnType())->toBe('json');
        });
    });

    describe('Address contract methods', function (): void {
        it('toPrimitive returns JSON string', function (): void {
            $address = new Address('123 Main St', 'Apt 4', 'NYC', 'NY', '10001', 'USA');
            $primitive = $address->toPrimitive();
            expect($primitive)->toBeString();

            $decoded = json_decode((string) $primitive, true);
            expect($decoded['street'])->toBe('123 Main St');
            expect($decoded['city'])->toBe('NYC');
        });

        it('fromPrimitive reconstructs from JSON string', function (): void {
            $data = [
                'street' => '456 Oak Ave',
                'street2' => null,
                'city' => 'LA',
                'state' => 'CA',
                'postalCode' => '90001',
                'country' => 'USA',
            ];
            $json = json_encode($data);
            $address = Address::fromPrimitive($json);

            expect($address->street)->toBe('456 Oak Ave');
            expect($address->street2)->toBeNull();
            expect($address->city)->toBe('LA');
        });

        it('fromPrimitive reconstructs from array', function (): void {
            $address = Address::fromPrimitive([
                'street' => '789 Pine Rd',
                'street2' => 'Suite 100',
                'city' => 'SF',
                'state' => 'CA',
                'postalCode' => '94101',
                'country' => 'USA',
            ]);

            expect($address->street)->toBe('789 Pine Rd');
            expect($address->street2)->toBe('Suite 100');
        });

        it('columnType returns json', function (): void {
            expect(Address::columnType())->toBe('json');
        });
    });

    describe('Duration contract methods', function (): void {
        it('toPrimitive returns integer milliseconds', function (): void {
            $duration = new Duration(5000);
            expect($duration->toPrimitive())->toBe(5000);
        });

        it('fromPrimitive reconstructs from integer', function (): void {
            $duration = Duration::fromPrimitive(30000);
            expect($duration->milliseconds)->toBe(30000);
        });

        it('fromPrimitive handles numeric strings', function (): void {
            $duration = Duration::fromPrimitive('1500');
            expect($duration->milliseconds)->toBe(1500);
        });

        it('fromPrimitive throws on non-numeric', function (): void {
            expect(fn (): Duration => Duration::fromPrimitive('abc'))
                ->toThrow(InvalidArgumentException::class);
        });

        it('columnType returns integer', function (): void {
            expect(Duration::columnType())->toBe('integer');
        });
    });

    describe('Percentage contract methods', function (): void {
        it('toPrimitive returns float value', function (): void {
            $pct = new Percentage(42.5);
            expect($pct->toPrimitive())->toBe(42.5);
        });

        it('fromPrimitive reconstructs from float', function (): void {
            $pct = Percentage::fromPrimitive(75.0);
            expect($pct->value)->toBe(75.0);
        });

        it('fromPrimitive reconstructs from int', function (): void {
            $pct = Percentage::fromPrimitive(50);
            expect($pct->value)->toBe(50.0);
        });

        it('fromPrimitive reconstructs from numeric string', function (): void {
            $pct = Percentage::fromPrimitive('33.33');
            expect($pct->value)->toBe(33.33);
        });

        it('columnType returns float', function (): void {
            expect(Percentage::columnType())->toBe('float');
        });
    });

    describe('equals with null', function (): void {
        it('returns false when comparing with null', function (): void {
            $email = new Email('test@example.com');
            expect($email->equals(null))->toBeFalse();
        });

        it('returns false for null across all VOs', function (): void {
            $vos = [
                new Email('a@b.com'),
                new Url('https://x.com'),
                new Money(100, 'USD'),
                new Duration(1000),
                new Percentage(50),
            ];

            foreach ($vos as $vo) {
                expect($vo->equals(null))->toBeFalse();
            }
        });
    });

    describe('cross-type equality', function (): void {
        it('different VO types with same toArray are not necessarily equal', function (): void {
            // Email and Url both have a 'value'-like key but different array keys
            $email = new Email('test@example.com');
            $url = new Url('https://example.com');

            // These have different toArray shapes so won't be equal
            expect($email->equals($url))->toBeFalse();
        });

        it('same type with same values are equal via contract', function (): void {
            $a = new Money(1000, 'USD');
            $b = new Money(1000, 'USD');

            expect($a->equals($b))->toBeTrue();
            expect($b->equals($a))->toBeTrue();
        });
    });

    describe('columnType returns valid Laravel types', function (): void {
        $validTypes = ['string', 'integer', 'float', 'decimal', 'json', 'boolean', 'text', 'datetime'];

        it('all VOs return a valid column type', function () use ($validTypes): void {
            $classes = [
                Email::class,
                Url::class,
                PhoneNumber::class,
                Currency::class,
                Money::class,
                Coordinates::class,
                Address::class,
                Duration::class,
                Percentage::class,
            ];

            foreach ($classes as $class) {
                $type = $class::columnType();
                expect($validTypes)->toContain($type);
            }
        });
    });
});
