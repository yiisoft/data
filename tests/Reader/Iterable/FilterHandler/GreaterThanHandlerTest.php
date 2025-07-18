<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Iterable\FilterHandler;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Yiisoft\Data\Reader\Filter\GreaterThan;
use Yiisoft\Data\Reader\Iterable\Context;
use Yiisoft\Data\Reader\Iterable\FilterHandler\GreaterThanHandler;
use Yiisoft\Data\Reader\Iterable\ValueReader\FlatValueReader;
use Yiisoft\Data\Tests\TestCase;

final class GreaterThanHandlerTest extends TestCase
{
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

        $context = new Context([], new FlatValueReader());

        $this->assertSame($expected, $handler->match($item, new GreaterThan('value', $value), $context));
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

        $context = new Context([], new FlatValueReader());

        $this->assertSame($expected, $handler->match($item, new GreaterThan('value', $value), $context));
    }
}
