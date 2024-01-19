<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Filter;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Yiisoft\Data\Reader\Filter\All;
use Yiisoft\Data\Reader\Filter\GreaterThan;
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
        ], $filter->toCriteriaArray());
    }

    public function testWithCriteriaArrayIsImmutable(): void
    {
        $filter = new All(
            new LessThan('test', 4),
            new GreaterThan('test', 2),
        );

        $newFilter = $filter->withCriteriaArray([
            ['>', 'test', 1],
            ['<', 'test', 5],
        ]);

        $this->assertNotSame($filter, $newFilter);
    }

    public function testWithCriteriaArrayOverridesConstructor(): void
    {
        $filter = new All(
            new LessThan('test', 4),
            new GreaterThan('test', 2),
        );

        $newFilter = $filter->withCriteriaArray([
            ['>', 'test', 1],
            ['<', 'test', 5],
        ]);

        $this->assertEquals(
            [
                'and',
                [
                    ['>', 'test', 1],
                    ['<', 'test', 5],
                ],
            ],
            $newFilter->toCriteriaArray()
        );
    }

    #[DataProvider('invalidFilterDataProvider')]
    public function testWithCriteriaArrayFailForInvalidFilter($filter): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid filter on "1" key.');

        (new All())->withCriteriaArray([['=', 'test', 1], $filter]);
    }

    #[DataProvider('invalidFilterOperatorDataProvider')]
    public function testWithCriteriaArrayFailForInvalidFilterOperator(array $filter): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid filter operator on "0" key.');

        (new All())->withCriteriaArray([$filter]);
    }
}
