<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Iterable\FilterHandler;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Yiisoft\Data\Reader\Filter\Equals;
use Yiisoft\Data\Reader\Iterable\FilterHandler\EqualsHandler;
use Yiisoft\Data\Reader\Iterable\IterableDataReader;
use Yiisoft\Data\Tests\Common\FixtureTrait;
use Yiisoft\Data\Tests\Common\Reader\FilterHandler\EqualsHandlerWithReaderTestTrait;
use Yiisoft\Data\Tests\Support\Car;
use Yiisoft\Data\Tests\TestCase;

final class EqualsHandlerTest extends TestCase
{
    use EqualsHandlerWithReaderTestTrait;
    use FixtureTrait;

    public static function matchScalarDataProvider(): array
    {
        return [
            [true, 45],
            [true, '45'],
            [false, 44],
            [false, 46],
        ];
    }

    #[DataProvider('matchScalarDataProvider')]
    public function testMatchScalar(bool $expected, mixed $value): void
    {
        $processor = new EqualsHandler();

        $item = [
            'id' => 1,
            'value' => 45,
        ];

        $this->assertSame($expected, $processor->match($item, new Equals('value', $value), []));
    }

    public static function matchDateTimeInterfaceDataProvider(): array
    {
        return [
            [true, new DateTimeImmutable('2022-02-22 16:00:45')],
            [false, new DateTimeImmutable('2022-02-22 16:00:44')],
            [false, new DateTimeImmutable('2022-02-22 16:00:46')],
        ];
    }

    #[DataProvider('matchDateTimeInterfaceDataProvider')]
    public function testMatchDateTimeInterface(bool $expected, DateTimeImmutable $value): void
    {
        $handler = new EqualsHandler();

        $item = [
            'id' => 1,
            'value' => new DateTimeImmutable('2022-02-22 16:00:45'),
        ];

        $this->assertSame($expected, $handler->match($item, new Equals('value', $value), []));
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
