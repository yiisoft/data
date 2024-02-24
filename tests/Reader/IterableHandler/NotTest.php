<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\IterableHandler;

use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;
use Yiisoft\Data\Reader\Filter\Equals;
use Yiisoft\Data\Reader\Filter\EqualsEmpty;
use Yiisoft\Data\Reader\Filter\Not;
use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\Iterable\FilterHandler\EqualsHandler;
use Yiisoft\Data\Reader\Iterable\FilterHandler\LikeHandler;
use Yiisoft\Data\Reader\Iterable\FilterHandler\NotHandler;
use Yiisoft\Data\Tests\Support\CustomFilter\FilterWithoutHandler;
use Yiisoft\Data\Tests\TestCase;

final class NotTest extends TestCase
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

        $this->assertSame($expected, $processor->match($item, new Not($filter), $filterHandlers));
    }

    public function testMatchFailIfFilterOperatorIsNotSupported(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Filter "' . FilterWithoutHandler::class . '" is not supported.');

        (new NotHandler())->match(['id' => 1], new Not(new FilterWithoutHandler()), []);
    }

    public function testInvalidFilter(): void
    {
        $handler = new NotHandler();
        $filter = new EqualsEmpty('test');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Incorrect filter.');
        $handler->match([], $filter, []);
    }
}
