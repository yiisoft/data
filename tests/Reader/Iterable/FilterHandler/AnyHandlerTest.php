<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Iterable\FilterHandler;

use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use Yiisoft\Data\Reader\Filter\OrX;
use Yiisoft\Data\Reader\Filter\Equals;
use Yiisoft\Data\Reader\Filter\GreaterThanOrEqual;
use Yiisoft\Data\Reader\Filter\LessThanOrEqual;
use Yiisoft\Data\Reader\Iterable\Context;
use Yiisoft\Data\Reader\Iterable\FilterHandler\OrXHandler;
use Yiisoft\Data\Reader\Iterable\FilterHandler\EqualsHandler;
use Yiisoft\Data\Reader\Iterable\FilterHandler\GreaterThanOrEqualHandler;
use Yiisoft\Data\Reader\Iterable\FilterHandler\LessThanOrEqualHandler;
use Yiisoft\Data\Reader\Iterable\ValueReader\FlatValueReader;
use Yiisoft\Data\Tests\Support\CustomFilter\FilterWithoutHandler;
use Yiisoft\Data\Tests\TestCase;

final class AnyHandlerTest extends TestCase
{
    public static function matchDataProvider(): array
    {
        $handlers = [
            Equals::class => new EqualsHandler(),
            GreaterThanOrEqual::class => new GreaterThanOrEqualHandler(),
            LessThanOrEqual::class => new LessThanOrEqualHandler(),
        ];
        return [
            [
                true,
                [new Equals('value', 45), new GreaterThanOrEqual('value', 45), new LessThanOrEqual('value', 45)],
                $handlers,
            ],
            [
                true,
                [new Equals('value', '45'), new GreaterThanOrEqual('value', 45), new LessThanOrEqual('value', 45)],
                $handlers,
            ],
            [
                true,
                [new Equals('value', 45), new GreaterThanOrEqual('value', 45), new LessThanOrEqual('value', 45)],
                $handlers,
            ],
            [
                true,
                [new Equals('value', 45), new GreaterThanOrEqual('value', 46), new LessThanOrEqual('value', 45)],
                $handlers,
            ],
            [
                true,
                [new Equals('value', 45), new GreaterThanOrEqual('value', 45), new LessThanOrEqual('value', 44)],
                $handlers,
            ],
            [
                false,
                [new Equals('value', 44), new GreaterThanOrEqual('value', 46), new LessThanOrEqual('value', 44)],
                $handlers,
            ],
        ];
    }

    #[DataProvider('matchDataProvider')]
    public function testMatch(bool $expected, array $filters, array $filterHandlers): void
    {
        $handler = new OrXHandler();

        $item = [
            'id' => 1,
            'value' => 45,
        ];

        $context = new Context($filterHandlers, new FlatValueReader());

        $this->assertSame($expected, $handler->match($item, new OrX(...$filters), $context));
    }

    public function testMatchFailIfFilterOperatorIsNotSupported(): void
    {
        $handler = new OrXHandler();
        $item = ['id' => 1];
        $filter = new OrX(new FilterWithoutHandler());
        $context = new Context([], new FlatValueReader());

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Filter "' . FilterWithoutHandler::class . '" is not supported.');
        $handler->match($item, $filter, $context);
    }
}
