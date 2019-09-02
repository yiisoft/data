<?php

namespace Yiisoft\Data\Tests\Reader;

use Yiisoft\Data\Reader\Sort;
use Yiisoft\Data\Tests\TestCase;

final class SortTest extends TestCase
{
    public function testInvalidConfigWithoutFieldName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Sort([
            1 => [],
        ]);
    }

    public function testInvalidConfigWithoutConfig(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Sort([
            'field' => 'whatever',
        ]);
    }

    public function testConfig(): void
    {
        $sort = new Sort([
            'a',
            'b' => [
                'default' => 'desc',
            ],
            'name' => [
                'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                'default' => 'desc',
                'label' => 'Name',
            ],
        ]);

        $expected = [
            'a' => [
                'asc' => [
                    'a' => SORT_ASC,
                ],
                'desc' => [
                    'a' => SORT_DESC,
                ],
                'default' => 'asc',
                'label' => 'a',
            ],
            'b' => [
                'asc' => [
                    'b' => SORT_ASC,
                ],
                'desc' => [
                    'b' => SORT_DESC,
                ],
                'default' => 'desc',
                'label' => 'b',
            ],
            'name' => [
                'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                'default' => 'desc',
                'label' => 'Name',
            ],
        ];
        $this->assertSame($expected, $this->getInaccessibleProperty($sort, 'config'));
    }

    public function testWithOrderStringIsImmutable(): void
    {
        $sort = new Sort([]);
        $this->assertNotSame($sort, $sort->withOrderString('a'));
    }

    public function testWithOrderIsImmutable(): void
    {
        $sort = new Sort([]);
        $this->assertNotSame($sort, $sort->withOrder([]));
    }

    public function testWithOrderString(): void
    {
        $sort = (new Sort([]))
            ->withOrderString(' -a, b');

        $this->assertSame([
            'a' => 'desc',
            'b' => 'asc',
        ], $sort->getOrder());
    }

    public function testGetOrderAsString(): void
    {
        $sort = (new Sort([]))
            ->withOrder([
                'a' => 'desc',
                'b' => 'asc',
            ]);

        $this->assertSame('-a,b', $sort->getOrderAsString());
    }

    public function testGetCriteriaWithEmptyConfig(): void
    {
        $sort = (new Sort([]))
            ->withOrder([
                'a' => 'desc',
                'b' => 'asc',
            ]);

        $this->assertSame([], $sort->getCriteria());
    }

    public function testGetCriteria(): void
    {
        $sort = (new Sort([
            'b' => [
                'asc' => ['bee' => SORT_ASC],
                'desc' => ['bee' => SORT_DESC],
                'default' => 'asc',
                'label' => 'B',
            ]
        ]))
            ->withOrder([
                'a' => 'desc',
                'b' => 'asc',
            ]);

        $this->assertSame([
            'bee' => SORT_ASC,
        ], $sort->getCriteria());
    }
}
