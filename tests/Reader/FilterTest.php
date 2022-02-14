<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader;

use InvalidArgumentException;
use Yiisoft\Data\Reader\Filter\All;
use Yiisoft\Data\Reader\Filter\Any;
use Yiisoft\Data\Reader\Filter\Between;
use Yiisoft\Data\Reader\Filter\Equals;
use Yiisoft\Data\Reader\Filter\EqualsEmpty;
use Yiisoft\Data\Reader\Filter\EqualsNull;
use Yiisoft\Data\Reader\Filter\FilterInterface;
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
            'Between' => [
                new Between('test', 42, 44),
                ['between', 'test', 42, 44],
            ],
            'Equals' => [
                new Equals('test', 42),
                ['=', 'test', 42],
            ],
            'EqualsEmpty' => [
                new EqualsEmpty('test'),
                ['empty', 'test'],
            ],
            'EqualsNull' => [
                new EqualsNull('test'),
                ['null', 'test'],
            ],
            'In' => [
                new In('test', [1, 2, 3]),
                ['in', 'test', [1, 2, 3]],
            ],
            'GreaterThan' => [
                new GreaterThan('test', 42),
                ['>', 'test', 42],
            ],
            'GreaterThanOrEqual' => [
                new GreaterThanOrEqual('test', 42),
                ['>=', 'test', 42],
            ],
            'LessThan' => [
                new LessThan('test', 42),
                ['<', 'test', 42],
            ],
            'LessThanOrEqual' => [
                new LessThanOrEqual('test', 42),
                ['<=', 'test', 42],
            ],
            'Like' => [
                new Like('test', '42'),
                ['like', 'test', '42'],
            ],
            'Not' => [
                new Not(new Equals('test', 42)),
                ['not', ['=', 'test', 42]],
            ],
            'Any' => [
                new Any(
                    new Equals('test', 1),
                    new Equals('test', 2)
                ),
                ['or', [
                    ['=', 'test', 1],
                    ['=', 'test', 2],
                ]],
            ],
            'All' => [
                new All(
                    new LessThan('test', 3),
                    new GreaterThan('test', 2)
                ),
                ['and', [
                    ['<', 'test', 3],
                    ['>', 'test', 2],
                ]],
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
                    'and',
                    [
                        ['=', 'test', 42],
                        [
                            'or',
                            [
                                ['=', 'test', 1],
                                ['=', 'test', 2],
                            ],
                        ],
                    ],
                ],
            ],
            'withFiltersArrayAll' => [
                (new All())->withFiltersArray([
                    ['<', 'test', 3],
                ]),
                [
                    'and',
                    [
                        ['<', 'test', 3],
                    ],
                ],
            ],
            'withFiltersArrayAny' => [
                (new Any())->withFiltersArray([
                    ['<', 'test1', 3],
                    new Equals('test2', 33),
                ]),
                [
                    'or',
                    [
                        ['<', 'test1', 3],
                        ['=', 'test2', 33],
                    ],
                ],
            ],
            'withFiltersArrayNestedGroup' => [
                (new All())->withFiltersArray([
                    ['<', 'test', 3],
                    [
                        'and',
                        [
                            ['>', 'test2', 99],
                        ],
                    ],
                ]),
                [
                    'and',
                    [
                        ['<', 'test', 3],
                        [
                            'and',
                            [
                                ['>', 'test2', 99],
                            ],
                        ],
                    ],
                ],
            ],
            'withFiltersArrayEmpty' => [
                (new All())->withFiltersArray([]),
                [
                    'and',
                    [],
                ],
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

    /**
     * @dataProvider arrayFailDataProvider
     */
    public function testWithFiltersArrayFail(array $filtersArray): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new All())->withFiltersArray($filtersArray);
    }

    public function arrayFailDataProvider(): array
    {
        return [
            'filterIsNotArray' => [
                ['test'],
            ],
            'emptyFilterArray' => [
                [[]],
            ],
            'operatorFailInvalidType' => [
                [[false]],
            ],
            'operatorFailWithEmptyString' => [
                [['']],
            ],
        ];
    }
}
