<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Iterable\FilterHandler;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Yiisoft\Data\Reader\Filter\LessThan;
use Yiisoft\Data\Reader\Iterable\Context;
use Yiisoft\Data\Reader\Iterable\FilterHandler\LessThanHandler;
use Yiisoft\Data\Reader\Iterable\ValueReader\FlatValueReader;
use Yiisoft\Data\Tests\TestCase;

final class LessThanHandlerTest extends TestCase
{
    public static function matchScalarDataProvider(): array
    {
        return [
            [true,  46],
            [true, '46'],
            [false, 45],
            [false, 44],
        ];
    }

    #[DataProvider('matchScalarDataProvider')]
    public function testMatchScalar(bool $expected, mixed $value): void
    {
        $processor = new LessThanHandler();

        $item = [
            'id' => 1,
            'value' => 45,
        ];

        $context = new Context([], new FlatValueReader());

        $this->assertSame($expected, $processor->match($item, new LessThan('value', $value), $context));
    }

    public static function matchDateTimeInterfaceDataProvider(): array
    {
        return [
            [true,  new DateTimeImmutable('2022-02-22 16:00:46')],
            [false, new DateTimeImmutable('2022-02-22 16:00:45')],
            [false, new DateTimeImmutable('2022-02-22 16:00:44')],
        ];
    }

    #[DataProvider('matchDateTimeInterfaceDataProvider')]
    public function testMatchDateTimeInterface(bool $expected, DateTimeImmutable $value): void
    {
        $handler = new LessThanHandler();

        $item = [
            'id' => 1,
            'value' => new DateTimeImmutable('2022-02-22 16:00:45'),
        ];

        $context = new Context([], new FlatValueReader());

        $this->assertSame($expected, $handler->match($item, new LessThan('value', $value), $context));
    }
}
