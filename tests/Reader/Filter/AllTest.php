<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Filter;

use InvalidArgumentException;
use Yiisoft\Data\Reader\Filter\All;
use Yiisoft\Data\Reader\Filter\Any;
use Yiisoft\Data\Reader\Filter\Between;
use Yiisoft\Data\Reader\Filter\Equals;
use Yiisoft\Data\Reader\Filter\GreaterThan;
use Yiisoft\Data\Reader\Filter\In;
use Yiisoft\Data\Reader\Filter\LessThan;
use Yiisoft\Data\Tests\TestCase;

final class AllTest extends TestCase
{
    public function testToArray(): void
    {
        $filter = new All(
            new LessThan('test', 4),
            new GreaterThan('test', 2),
        );

        $this->assertSame([
            'and',
            [
                ['<', 'test', 4],
                ['>', 'test', 2],
            ],
        ], $filter->toArray());
    }

    public function testToArrayAndWithFiltersArray(): void
    {
        $filter = new All(
            new LessThan('test', 4),
            new GreaterThan('test', 2),
        );

        $newFilter = $filter->withFiltersArray([
            new Any(
                new Between('test', 2, 4),
                new In('test', [1, 2, 3, 4]),
            ),
            ['=', 'test', 3],
        ]);

        $this->assertNotSame($filter, $newFilter);

        $this->assertSame([
            'and',
            [
                [
                    'or',
                    [
                        ['between', 'test', 2, 4],
                        ['in', 'test', [1, 2, 3, 4]],
                    ],
                ],
                ['=', 'test', 3],
            ],
        ], $newFilter->toArray());
    }

    /**
     * @dataProvider invalidFilterDataProvider
     */
    public function testWithFiltersArrayFailForInvalidFilter($filter): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid filter on "1" key.');

        (new All())->withFiltersArray([new Equals('test', 1), $filter]);
    }

    /**
     * @dataProvider invalidFilterOperatorDataProvider
     */
    public function testWithFiltersArrayFailForInvalidFilterOperator(array $filter): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid filter operator on "0" key.');

        (new All())->withFiltersArray([$filter]);
    }
}
