<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Iterable\FilterHandler;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Yiisoft\Data\Reader\Filter\LessThanOrEqual;
use Yiisoft\Data\Reader\Iterable\FilterHandler\LessThanOrEqualHandler;
use Yiisoft\Data\Tests\Common\FixtureTrait;
use Yiisoft\Data\Tests\Common\Reader\FilterHandler\LessThanOrEqualHandlerWithReaderTestTrait;
use Yiisoft\Data\Tests\Common\Reader\ReaderTrait;
use Yiisoft\Data\Tests\TestCase;

final class LessThanOrEqualHandlerTest extends TestCase
{
    use FixtureTrait;
    use LessThanOrEqualHandlerWithReaderTestTrait;
    use ReaderTrait;

    public static function matchScalarDataProvider(): array
    {
        return [
            [true, 46],
            [true, 45],
            [true, '45'],
            [false, 44],
        ];
    }

    #[DataProvider('matchScalarDataProvider')]
    public function testMatchScalar(bool $expected, mixed $value): void
    {
        $handler = new LessThanOrEqualHandler();

        $item = [
            'id' => 1,
            'value' => 45,
        ];

        $this->assertSame($expected, $handler->match($item, new LessThanOrEqual('value', $value), []));
    }

    public static function matchDateTimeInterfaceDataProvider(): array
    {
        return [
            [true, new DateTimeImmutable('2022-02-22 16:00:46')],
            [true, new DateTimeImmutable('2022-02-22 16:00:45')],
            [false, new DateTimeImmutable('2022-02-22 16:00:44')],
        ];
    }

    #[DataProvider('matchDateTimeInterfaceDataProvider')]
    public function testMatchDateTimeInterface(bool $expected, DateTimeImmutable $value): void
    {
        $handler = new LessThanOrEqualHandler();

        $item = [
            'id' => 1,
            'value' => new DateTimeImmutable('2022-02-22 16:00:45'),
        ];

        $this->assertSame($expected, $handler->match($item, new LessThanOrEqual('value', $value), []));
    }
}