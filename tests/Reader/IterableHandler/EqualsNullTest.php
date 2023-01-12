<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\IterableHandler;

use InvalidArgumentException;
use Yiisoft\Data\Reader\Filter\EqualsNull as EqualsNullFilter;
use Yiisoft\Data\Reader\Iterable\IterableDataReader;
use Yiisoft\Data\Tests\Support\Car;
use Yiisoft\Data\Reader\Iterable\FilterHandler\EqualsNullHandler;
use Yiisoft\Data\Tests\TestCase;

final class EqualsNullTest extends TestCase
{
    public function matchDataProvider(): array
    {
        return [
            [true, ['value' => null]],
            [false, ['value' => false]],
            [false, ['value' => true]],
            [false, ['value' => 0]],
            [false, ['value' => 0.0]],
            [false, ['value' => 42]],
            [false, ['value' => '']],
            [false, ['value' => 'null']],
        ];
    }

    /**
     * @dataProvider matchDataProvider
     */
    public function testMatch(bool $expected, array $item): void
    {
        $this->assertSame($expected, (new EqualsNullHandler())->match($item, ['value'], []));
    }

    public function invalidCountArgumentsDataProvider(): array
    {
        return [
            'zero' => [[]],
            'two' => [[1, 2]],
            'three' => [[1, 2, 3]],
            'four' => [[1, 2, 3, 4]],
        ];
    }

    /**
     * @dataProvider invalidCountArgumentsDataProvider
     */
    public function testMatchFailForInvalidCountArguments($arguments): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$arguments should contain exactly one element.');

        (new EqualsNullHandler())->match(['id' => 1], $arguments, []);
    }

    /**
     * @dataProvider invalidStringValueDataProvider
     */
    public function testMatchFailForInvalidFieldValue($field): void
    {
        $type = get_debug_type($field);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The field should be string. The $type is received.");

        (new EqualsNullHandler())->match(['id' => 1], [$field], []);
    }

    public function testObjectWithGetters(): void
    {
        $car1 = new Car(1);
        $car2 = new Car(2);
        $car3 = new Car(null);
        $car4 = new Car(4);
        $car5 = new Car(null);

        $reader = new IterableDataReader([
            1 => $car1,
            2 => $car2,
            3 => $car3,
            4 => $car4,
            5 => $car5,
        ]);

        $result = $reader->withFilter(new EqualsNullFilter('getNumber()'))->read();

        $this->assertSame([3 => $car3, 5 => $car5], $result);
    }
}
