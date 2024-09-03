<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Iterable\FilterHandler;

use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use Yiisoft\Data\Reader\Filter\All;
use Yiisoft\Data\Reader\Filter\Equals;
use Yiisoft\Data\Reader\Filter\GreaterThanOrEqual;
use Yiisoft\Data\Reader\Filter\LessThanOrEqual;
use Yiisoft\Data\Reader\Iterable\FilterHandler\AllHandler;
use Yiisoft\Data\Reader\Iterable\FilterHandler\EqualsHandler;
use Yiisoft\Data\Reader\Iterable\FilterHandler\GreaterThanOrEqualHandler;
use Yiisoft\Data\Reader\Iterable\FilterHandler\LessThanOrEqualHandler;
use Yiisoft\Data\Tests\Common\Reader\ReaderWithFilter\BaseReaderWithAllTestCase;
use Yiisoft\Data\Tests\Reader\Iterable\ReaderWithFilter\ReaderTrait;
use Yiisoft\Data\Tests\Support\CustomFilter\FilterWithoutHandler;

final class AllHandlerTestBase extends BaseReaderWithAllTestCase
{
    use ReaderTrait;

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
                false,
                [new Equals('value', 44), new GreaterThanOrEqual('value', 45), new LessThanOrEqual('value', 45)],
                $handlers,
            ],
            [
                false,
                [new Equals('value', 45), new GreaterThanOrEqual('value', 46), new LessThanOrEqual('value', 45)],
                $handlers,
            ],
            [
                false,
                [new Equals('value', 45), new GreaterThanOrEqual('value', 45), new LessThanOrEqual('value', 44)],
                $handlers,
            ],
            [
                false,
                [new Equals('value', 45), new GreaterThanOrEqual('value', 45), new LessThanOrEqual('value', 44)],
                $handlers,
            ],
        ];
    }

    #[DataProvider('matchDataProvider')]
    public function testMatch(bool $expected, array $filters, array $filterHandlers): void
    {
        $handler = new AllHandler();

        $item = [
            'id' => 1,
            'value' => 45,
        ];

        $this->assertSame($expected, $handler->match($item, new All(...$filters), $filterHandlers));
    }

    public function testMatchFailIfFilterOperatorIsNotSupported(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Filter "' . FilterWithoutHandler::class . '" is not supported.');

        (new AllHandler())->match(['id' => 1], new All(new FilterWithoutHandler()), []);
    }
}
