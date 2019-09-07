<?php

namespace Yiisoft\Data\Tests\Reader;

use Yiisoft\Data\Reader\Filter\All;
use Yiisoft\Data\Reader\Filter\Any;
use Yiisoft\Data\Reader\Filter\FilterInterface;
use Yiisoft\Data\Reader\Filter\Equals;
use Yiisoft\Data\Reader\Filter\GreaterThan;
use Yiisoft\Data\Reader\Filter\GreaterThanOrEqual;
use Yiisoft\Data\Reader\Filter\In;
use Yiisoft\Data\Reader\Filter\LessThan;
use Yiisoft\Data\Reader\Filter\LessThanOrEqual;
use Yiisoft\Data\Reader\Filter\Like;
use Yiisoft\Data\Reader\Filter\Not;
use Yiisoft\Data\Tests\TestCase;

final class FilterTest extends TestCase
{
    public function filterDataProvider(): array
    {
        return [
            'Equals' => [
                new Equals('test', 42),
                ['eq', 'test', 42],
            ],
            'In' => [
                new In('test', [1, 2, 3]),
                ['in', 'test', [1, 2, 3]],
            ],
            'GreaterThan' => [
                new GreaterThan('test', 42),
                ['gt', 'test', 42],
            ],
            'GreaterThanOrEqual' => [
                new GreaterThanOrEqual('test', 42),
                ['gte', 'test', 42],
            ],
            'LessThan' => [
                new LessThan('test', 42),
                ['lt', 'test', 42],
            ],
            'LessThanOrEqual' => [
                new LessThanOrEqual('test', 42),
                ['lte', 'test', 42],
            ],
            'Like' => [
                new Like('test', 42),
                ['like', 'test', '42'],
            ],
            'Not' => [
                new Not(new Equals('test', 42)),
                ['not', ['eq', 'test', 42]],
            ],
            'Any' => [
                new Any(
                    new Equals('test', 1),
                    new Equals('test', 2)
                ),
                ['any', [
                    ['eq', 'test', 1],
                    ['eq', 'test', 2],
                ]]
            ],
            'All' => [
                new All(
                    new LessThan('test', 3),
                    new GreaterThan('test', 2)
                ),
                ['all', [
                    ['lt', 'test', 3],
                    ['gt', 'test', 2],
                ]]
            ],
            'NestedGroup' => [
                new All(
                    new Equals('test', 42),
                    new Any(
                        new Equals('test', 1),
                        new Equals('test', 2)
                    )
                ),
                [
                    'all',
                    [
                        ['eq', 'test', 42],
                        [
                            'any',
                            [
                                ['eq', 'test', 1],
                                ['eq', 'test', 2],
                            ]
                        ]
                    ]
                ]
            ],
        ];
    }

    /**
     * @dataProvider filterDataProvider
     */
    public function testFilter(FilterInterface $filter, array $filterArray): void
    {
        $this->assertSame($filterArray, $filter->toArray());
    }
}
