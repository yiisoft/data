<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\IterableHandler;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Yiisoft\Data\Reader\Filter\Equals;
use Yiisoft\Data\Reader\Iterable\FilterHandler\EqualsHandler;
use Yiisoft\Data\Reader\Iterable\IterableDataReader;
use Yiisoft\Data\Tests\Support\Car;
use Yiisoft\Data\Tests\TestCase;

final class EqualsTest extends TestCase
{
    public static function matchScalarDataProvider(): array
    {
        return [
            [true, ['value', 45]],
            [true, ['value', '45']],
            [false, ['value', 44]],
            [false, ['value', 46]],
        ];
    }

    #[DataProvider('matchScalarDataProvider')]
    public function testMatchScalar(bool $expected, array $arguments): void
    {
        $processor = new EqualsHandler();

        $item = [
            'id' => 1,
            'value' => 45,
        ];

        $this->assertSame($expected, $processor->match($item, $arguments, []));
    }

    public static function matchDateTimeInterfaceDataProvider(): array
    {
        return [
            [true, ['value', new DateTimeImmutable('2022-02-22 16:00:45')]],
            [false, ['value', new DateTimeImmutable('2022-02-22 16:00:44')]],
            [false, ['value', new DateTimeImmutable('2022-02-22 16:00:46')]],
        ];
    }

    #[DataProvider('matchDateTimeInterfaceDataProvider')]
    public function testMatchDateTimeInterface(bool $expected, array $arguments): void
    {
        $processor = new EqualsHandler();

        $item = [
            'id' => 1,
            'value' => new DateTimeImmutable('2022-02-22 16:00:45'),
        ];

        $this->assertSame($expected, $processor->match($item, $arguments, []));
    }

    public static function invalidCountArgumentsDataProvider(): array
    {
        return [
            'zero' => [[]],
            'one' => [[1]],
            'three' => [[1, 2, 3]],
            'four' => [[1, 2, 3, 4]],
        ];
    }

    #[DataProvider('invalidCountArgumentsDataProvider')]
    public function testMatchFailForInvalidCountArguments($arguments): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$arguments should contain exactly two elements.');

        (new EqualsHandler())->match(['id' => 1], $arguments, []);
    }

    #[DataProvider('invalidStringValueDataProvider')]
    public function testMatchFailForInvalidFieldValue($field): void
    {
        $type = get_debug_type($field);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The field should be string. The $type is received.");

        (new EqualsHandler())->match(['id' => 1], [$field, 1], []);
    }

    public function testObjectWithGetters(): void
    {
        $car1 = new Car(1);
        $car2 = new Car(2);
        $car3 = new Car(1);
        $car4 = new Car(3);
        $car5 = new Car(5);

        $reader = new IterableDataReader([
            1 => $car1,
            2 => $car2,
            3 => $car3,
            4 => $car4,
            5 => $car5,
        ]);

        $result = $reader->withFilter(new Equals('getNumber()', 1))->read();

        $this->assertSame([1 => $car1, 3 => $car3], $result);
    }
}
