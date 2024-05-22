<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Iterable\FilterHandler;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Yiisoft\Data\Reader\Filter\GreaterThan;
use Yiisoft\Data\Reader\Iterable\FilterHandler\GreaterThanHandler;
use Yiisoft\Data\Tests\Common\FixtureTrait;
use Yiisoft\Data\Tests\Common\Reader\FilterHandler\GreaterThanHandlerWithReaderTestTrait;
use Yiisoft\Data\Tests\Common\Reader\ReaderTrait;
use Yiisoft\Data\Tests\TestCase;

final class GreaterThanHandlerTest extends TestCase
{
    use FixtureTrait;
    use GreaterThanHandlerWithReaderTestTrait;
    use ReaderTrait;

    public static function matchScalarDataProvider(): array
    {
        return [
            [true, 44],
            [true, '44'],
            [false, 45],
            [false, 46],
        ];
    }

    #[DataProvider('matchScalarDataProvider')]
    public function testMatchScalar(bool $expected, mixed $value): void
    {
        $handler = new GreaterThanHandler();

        $item = [
            'id' => 1,
            'value' => 45,
        ];

        $this->assertSame($expected, $handler->match($item, new GreaterThan('value', $value), []));
    }

    public static function matchDateTimeInterfaceDataProvider(): array
    {
        return [
            [true, new DateTimeImmutable('2022-02-22 16:00:44')],
            [false, new DateTimeImmutable('2022-02-22 16:00:45')],
            [false, new DateTimeImmutable('2022-02-22 16:00:46')],
        ];
    }

    #[DataProvider('matchDateTimeInterfaceDataProvider')]
    public function testMatchDateTimeInterface(bool $expected, DateTimeImmutable $value): void
    {
        $handler = new GreaterThanHandler();

        $item = [
            'id' => 1,
            'value' => new DateTimeImmutable('2022-02-22 16:00:45'),
        ];

        $this->assertSame($expected, $handler->match($item, new GreaterThan('value', $value), []));
    }
}
