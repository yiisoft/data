<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader;

use InvalidArgumentException;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Data\Tests\TestCase;

final class SortTest extends TestCase
{
    public function testInvalidConfigWithoutFieldName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid config format.');

        Sort::only([
            1 => [],
        ]);
    }

    public function testInvalidConfigWithoutConfig(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid config format.');

        Sort::only([
            'field' => 'whatever',
        ]);
    }

    public function testConfig(): void
    {
        $config = [
            'a',
            'b' => [
                'default' => 'desc',
            ],
            'name' => [
                'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                'default' => 'desc',
            ],
        ];

        $sortOnly = Sort::only($config);
        $sortAny = Sort::any($config);

        $expected = [
            'a' => [
                'asc' => [
                    'a' => SORT_ASC,
                ],
                'desc' => [
                    'a' => SORT_DESC,
                ],
                'default' => 'asc',
            ],
            'b' => [
                'asc' => [
                    'b' => SORT_ASC,
                ],
                'desc' => [
                    'b' => SORT_DESC,
                ],
                'default' => 'desc',
            ],
            'name' => [
                'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                'default' => 'desc',
            ],
        ];

        $this->assertSame($expected, $this->getInaccessibleProperty($sortAny, 'config'));
        $this->assertSame($expected, $this->getInaccessibleProperty($sortOnly, 'config'));
    }

    public function testWithOrderStringIsImmutable(): void
    {
        $sort = Sort::any();
        $this->assertNotSame($sort, $sort->withOrderString('a'));
    }

    public function testWithOrderIsImmutable(): void
    {
        $sort = Sort::any();
        $this->assertNotSame($sort, $sort->withOrder([]));
    }

    public function testWithOrderString(): void
    {
        $sort = Sort::any()->withOrderString(' -a, b');

        $this->assertSame([
            'a' => 'desc',
            'b' => 'asc',
        ], $sort->getOrder());
    }

    public function testGetOrderAsString(): void
    {
        $sort = Sort::any()->withOrder([
            'a' => 'desc',
            'b' => 'asc',
        ]);

        $this->assertSame('-a,b', $sort->getOrderAsString());
    }

    public function testOnlyModeGetCriteriaWithEmptyConfig(): void
    {
        $sort = Sort::only([])->withOrder([
            'a' => 'desc',
            'b' => 'asc',
        ]);

        $this->assertSame([], $sort->getCriteria());
    }

    public function testAnyModeGetCriteriaWithEmptyConfig(): void
    {
        $sort = Sort::any([])->withOrder([
            'a' => 'desc',
            'b' => 'asc',
        ]);

        $this->assertSame(['a' => 'desc', 'b' => 'asc'], $sort->getCriteria());
    }

    public function testGetCriteria(): void
    {
        $sort = Sort::only([
            'b' => [
                'asc' => ['bee' => SORT_ASC],
                'desc' => ['bee' => SORT_DESC],
                'default' => 'asc',
            ],
        ])->withOrder([
            'a' => 'desc',
            'b' => 'asc',
        ]);

        $this->assertSame([
            'bee' => SORT_ASC,
        ], $sort->getCriteria());
    }

    public function testAnyModeGetCriteriaWhenAnyFieldConflictsWithConfig(): void
    {
        $sort = Sort::any([
            'a' => [
                'asc' => ['foo' => 'asc'],
                'desc' => ['foo' => 'desc'],
                'default' => 'asc',
            ],
            'b' => [
                'asc' => ['bee' => 'asc'],
                'desc' => ['bee' => 'desc'],
                'default' => 'asc',
            ],
        ])->withOrderString('-bee,b,a,-foo');

        $this->assertSame([
            'bee' => 'desc',
            'foo' => 'asc',
        ], $sort->getCriteria());
    }

    public function testGetCriteriaDefaults(): void
    {
        $sort = Sort::only([
            'b' => [
                'asc' => ['bee' => SORT_ASC],
                'desc' => ['bee' => SORT_DESC],
                'default' => 'desc',
            ],
        ])->withOrder([]);

        $this->assertSame([
            'bee' => SORT_DESC,
        ], $sort->getCriteria());
    }

    public function testGetCriteriaDefaultsWithSimpleConfig(): void
    {
        $sort = Sort::only(['a', 'b'])->withOrder([]);

        $this->assertSame([], $sort->getOrder());
    }

    public function testGetCriteriaOrder(): void
    {
        $sort = Sort::only([
            'b',
            'c',
        ])->withOrder(['c' => 'desc']);

        $this->assertSame([
            'c' => SORT_DESC,
            'b' => SORT_ASC,
        ], $sort->getCriteria());
    }

    public function testGetCriteriaDefaultsWhenConfigIsNotComplete(): void
    {
        $sort = Sort::only([
            'b' => [
                'asc' => ['bee' => SORT_ASC],
                'desc' => ['bee' => SORT_DESC],
            ],
        ])->withOrder([]);

        $this->assertSame([
            'bee' => SORT_ASC,
        ], $sort->getCriteria());
    }

    public function testGetCriteriaWithShortFieldSyntax(): void
    {
        $sort = Sort::only([
            'id',
            'name',
        ])->withOrder(['name' => 'desc']);

        $this->assertSame([
            'name' => SORT_DESC,
            'id' => SORT_ASC,
        ], $sort->getCriteria());
    }

    public function testWithoutDefaultSortingWhenFormingCriteria(): void
    {
        $sort = Sort::only([
            'a',
            'b' => [
                'asc' => ['bee' => SORT_ASC],
                'desc' => ['bee' => SORT_DESC],
                'default' => 'asc',
            ],
        ])
            ->withOrder(
                [
                    'b' => 'desc',
                ]
            )
            ->withoutDefaultSorting();

        $this->assertSame(
            [
                'bee' => SORT_DESC,
            ],
            $sort->getCriteria()
        );
    }
}
