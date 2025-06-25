<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Iterable\FilterHandler;

use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use Yiisoft\Data\Reader\Filter\Equals;
use Yiisoft\Data\Reader\Filter\Not;
use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\Iterable\Context;
use Yiisoft\Data\Reader\Iterable\FilterHandler\EqualsHandler;
use Yiisoft\Data\Reader\Iterable\FilterHandler\NotHandler;
use Yiisoft\Data\Reader\Iterable\ValueReader\FlatValueReader;
use Yiisoft\Data\Tests\Support\CustomFilter\FilterWithoutHandler;
use Yiisoft\Data\Tests\TestCase;

final class NotHandlerTest extends TestCase
{
    public static function matchDataProvider(): array
    {
        return [
            [true, new Equals('value', 44), [Equals::class => new EqualsHandler()]],
            [true, new Equals('value', 46), [Equals::class => new EqualsHandler()]],
            [false, new Equals('value', 45), [Equals::class => new EqualsHandler()]],
            [false, new Equals('value', '45'), [Equals::class => new EqualsHandler()]],
        ];
    }

    #[DataProvider('matchDataProvider')]
    public function testMatch(bool $expected, FilterInterface $filter, array $filterHandlers): void
    {
        $processor = new NotHandler();

        $item = [
            'id' => 1,
            'value' => 45,
        ];

        $context = new Context($filterHandlers, new FlatValueReader());

        $this->assertSame($expected, $processor->match($item, new Not($filter), $context));
    }

    public function testMatchFailIfFilterOperatorIsNotSupported(): void
    {
        $handler = new NotHandler();
        $item = ['id' => 1];
        $filter = new Not(new FilterWithoutHandler());
        $context = new Context([], new FlatValueReader());

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Filter "' . FilterWithoutHandler::class . '" is not supported.');
        $handler->match($item, $filter, $context);
    }
}
