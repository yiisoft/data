<?php

namespace Yiisoft\Data\Tests\Reader;

use Yiisoft\Data\Reader\Filter;
use Yiisoft\Data\Tests\TestCase;

final class FilterTest extends TestCase
{
    /**
     * @test
     */
    public function invalidFieldTypesShouldThrowException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Filter(['test' => 'zoolean']);
    }

    /**
     * @test
     */
    public function invalidFieldNamesShouldThrowException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Filter([42 => 'boolean']);
    }

//$config = [
//            'or' => [
//                [
//                    'and' => [
//                        'test' => 42,
//                        'price' => 25,
//                    ],
//                ],
//                [
//                    'id' => ['in' => [2, 5, 8]],
//                    'price' => ['gt' => 10, 'lt' => 50]
//                ]
//            ]
//        ];

    /**
     * @test
     */
    public function withCriteriaIsImmutable(): void
    {
        $filter = new Filter([]);
        $this->assertNotSame($filter, $filter->withCriteria([]));
    }

    /**
     * @test
     */
    public function validWithCriteriaShouldPass(): void
    {

    }
}




