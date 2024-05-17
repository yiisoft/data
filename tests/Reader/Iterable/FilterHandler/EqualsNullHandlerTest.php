<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Iterable\FilterHandler;

use PHPUnit\Framework\Attributes\DataProvider;
use Yiisoft\Data\Reader\Filter\EqualsNull;
use Yiisoft\Data\Reader\Iterable\FilterHandler\EqualsNullHandler;
use Yiisoft\Data\Reader\Iterable\IterableDataReader;
use Yiisoft\Data\Tests\Common\Reader\FilterHandler\EqualsNullHandlerWithReaderTestTrait;
use Yiisoft\Data\Tests\Common\Reader\ReaderTestTrait;
use Yiisoft\Data\Tests\Support\Car;
use Yiisoft\Data\Tests\TestCase;

final class EqualsNullHandlerTest extends TestCase
{
    use ReaderTestTrait;
    use EqualsNullHandlerWithReaderTestTrait;

    public static function matchDataProvider(): array
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

    #[DataProvider('matchDataProvider')]
    public function testMatch(bool $expected, array $item): void
    {
        $this->assertSame($expected, (new EqualsNullHandler())->match($item, new EqualsNull('value'), []));
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

        $result = $reader->withFilter(new EqualsNull('getNumber()'))->read();

        $this->assertSame([3 => $car3, 5 => $car5], $result);
    }
}
