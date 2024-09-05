<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Iterable\FilterHandler;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Yiisoft\Data\Reader\Filter\Between;
use Yiisoft\Data\Reader\Iterable\FilterHandler\BetweenHandler;
use Yiisoft\Data\Reader\Iterable\IterableDataReader;
use Yiisoft\Data\Tests\Support\Car;
use Yiisoft\Data\Tests\TestCase;

final class BetweenHandlerTest extends TestCase
{
    public static function matchScalarDataProvider(): array
    {
        return [
            [true, new Between('value', 42, 47)],
            [true, new Between('value', 45, 45)],
            [true, new Between('value', 45, 46)],
            [false, new Between('value', 46, 47)],
            [false, new Between('value', 46, 45)],
        ];
    }

    #[DataProvider('matchScalarDataProvider')]
    public function testMatchScalar(bool $expected, Between $filter): void
    {
        $handler = new BetweenHandler();

        $item = [
            'id' => 1,
            'value' => 45,
        ];

        $this->assertSame($expected, $handler->match($item, $filter, []));
    }

    public static function matchDateTimeInterfaceDataProvider(): array
    {
        return [
            [true, new DateTimeImmutable('2022-02-22 16:00:42'), new DateTimeImmutable('2022-02-22 16:00:47')],
            [true, new DateTimeImmutable('2022-02-22 16:00:45'), new DateTimeImmutable('2022-02-22 16:00:45')],
            [true, new DateTimeImmutable('2022-02-22 16:00:45'), new DateTimeImmutable('2022-02-22 16:00:46')],
            [false, new DateTimeImmutable('2022-02-22 16:00:46'), new DateTimeImmutable('2022-02-22 16:00:47')],
            [false, new DateTimeImmutable('2022-02-22 16:00:46'), new DateTimeImmutable('2022-02-22 16:00:45')],
        ];
    }

    #[DataProvider('matchDateTimeInterfaceDataProvider')]
    public function testMatchDateTimeInterface(bool $expected, DateTimeImmutable $from, DateTimeImmutable $to): void
    {
        $processor = new BetweenHandler();

        $item = [
            'id' => 1,
            'value' => new DateTimeImmutable('2022-02-22 16:00:45'),
        ];

        $this->assertSame($expected, $processor->match($item, new Between('value', $from, $to), []));
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
