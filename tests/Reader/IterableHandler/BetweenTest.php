<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\IterableHandler;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Yiisoft\Data\Reader\Filter\Between;
use Yiisoft\Data\Reader\Iterable\FilterHandler\BetweenHandler;
use Yiisoft\Data\Reader\Iterable\IterableDataReader;
use Yiisoft\Data\Tests\Support\Car;
use Yiisoft\Data\Tests\TestCase;

final class BetweenTest extends TestCase
{
    public static function matchScalarDataProvider(): array
    {
        return [
            [true, ['value', 42, 47]],
            [true, ['value', 45, 45]],
            [true, ['value', 45, 46]],
            [false, ['value', 46, 47]],
            [false, ['value', 46, 45]],
        ];
    }

    #[DataProvider('matchScalarDataProvider')]
    public function testMatchScalar(bool $expected, array $arguments): void
    {
        $processor = new BetweenHandler();

        $item = [
            'id' => 1,
            'value' => 45,
        ];

        $this->assertSame($expected, $processor->match($item, $arguments, []));
    }

    public static function matchDateTimeInterfaceDataProvider(): array
    {
        return [
            [true, ['value', new DateTimeImmutable('2022-02-22 16:00:42'), new DateTimeImmutable('2022-02-22 16:00:47')]],
            [true, ['value', new DateTimeImmutable('2022-02-22 16:00:45'), new DateTimeImmutable('2022-02-22 16:00:45')]],
            [true, ['value', new DateTimeImmutable('2022-02-22 16:00:45'), new DateTimeImmutable('2022-02-22 16:00:46')]],
            [false, ['value', new DateTimeImmutable('2022-02-22 16:00:46'), new DateTimeImmutable('2022-02-22 16:00:47')]],
            [false, ['value', new DateTimeImmutable('2022-02-22 16:00:46'), new DateTimeImmutable('2022-02-22 16:00:45')]],
        ];
    }

    #[DataProvider('matchDateTimeInterfaceDataProvider')]
    public function testMatchDateTimeInterface(bool $expected, array $arguments): void
    {
        $processor = new BetweenHandler();

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
            'two' => [[1, 2]],
            'four' => [[1, 2, 3, 4]],
        ];
    }

    #[DataProvider('invalidCountArgumentsDataProvider')]
    public function testMatchFailForInvalidCountArguments($arguments): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$arguments should contain exactly three elements.');

        (new BetweenHandler())->match(['id' => 1], $arguments, []);
    }

    #[DataProvider('invalidStringValueDataProvider')]
    public function testMatchFailForInvalidFieldValue($field): void
    {
        $type = get_debug_type($field);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The field should be string. The $type is received.");

        (new BetweenHandler())->match(['id' => 1], [$field, 1, 2], []);
    }

    public function testObjectWithGetters(): void
    {
        $car1 = new Car(1);
        $car2 = new Car(2);
        $car3 = new Car(3);
        $car4 = new Car(4);
        $car5 = new Car(5);

        $reader = new IterableDataReader([
            1 => $car1,
            2 => $car2,
            3 => $car3,
            4 => $car4,
            5 => $car5,
        ]);

        $result = $reader->withFilter(new Between('getNumber()', 3, 5))->read();

        $this->assertSame([3 => $car3, 4 => $car4, 5 => $car5], $result);
    }
}
