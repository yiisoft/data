<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Filter;

use InvalidArgumentException;
use Yiisoft\Data\Reader\Filter\Any;
use Yiisoft\Data\Reader\Filter\GreaterThan;
use Yiisoft\Data\Reader\Filter\LessThan;
use Yiisoft\Data\Tests\TestCase;

final class AnyTest extends TestCase
{
    public function testToArray(): void
    {
        $filter = new Any(
            new LessThan('test', 4),
            new GreaterThan('test', 2),
        );

        $this->assertSame([
            'or',
            [
                ['<', 'test', 4],
                ['>', 'test', 2],
            ],
        ], $filter->toCriteriaArray());
    }

    public function testWithCriteriaArrayIsImmutable(): void
    {
        $filter = new Any(
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
        $filter = new Any(
            new LessThan('test', 4),
            new GreaterThan('test', 2),
        );

        $newFilter = $filter->withCriteriaArray([
            ['>', 'test', 1],
            ['<', 'test', 5],
        ]);

        $this->assertEquals(
            [
                'or',
                [
                    ['>', 'test', 1],
                    ['<', 'test', 5],
                ],
            ],
            $newFilter->toCriteriaArray()
        );
    }

    /**
     * @dataProvider invalidFilterDataProvider
     */
    public function testWithCriteriaArrayFailForInvalidFilter(mixed $filter): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid filter on "1" key.');

        (new Any())->withCriteriaArray([
            ['=', 'test', 1],
            $filter,
        ]);
    }

    /**
     * @dataProvider invalidFilterOperatorDataProvider
     */
    public function testWithCriteriaArrayFailForInvalidFilterOperator(array $filter): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid filter operator on "0" key.');

        (new Any())->withCriteriaArray([$filter]);
    }
}
