<?php

namespace Yiisoft\Data\Tests\Reader;

use PHPUnit\Framework\TestCase;
use Yiisoft\Data\Reader\ArrayDataReader;
use Yiisoft\Data\Reader\Sort;

final class ArrayDataReaderTest extends TestCase
{
    private function getDataSet(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Codename Boris',
            ],
            [
                'id' => 2,
                'name' => 'Codename Doris',
            ],
            [
                'id' => 3,
                'name' => 'Agent K',
            ],
            [
                'id' => 5,
                'name' => 'Agent J',
            ],
            [
                'id' => 6,
                'name' => '007',
            ],
        ];
    }

    private function getDataSetSortedByName(): array
    {
        return [
            [
                'id' => 6,
                'name' => '007',
            ],
            [
                'id' => 5,
                'name' => 'Agent J',
            ],
            [
                'id' => 3,
                'name' => 'Agent K',
            ],
            [
                'id' => 1,
                'name' => 'Codename Boris',
            ],
            [
                'id' => 2,
                'name' => 'Codename Doris',
            ],
        ];
    }

    public function testWithLimitIsImmutable(): void
    {
        $reader = new ArrayDataReader([]);

        $this->assertNotSame($reader, $reader->withLimit(1));
    }

    public function testLimitIsApplied(): void
    {
        $reader = (new ArrayDataReader($this->getDataSet()))->withLimit(5);

        $data = $reader->read();

        $this->assertCount(5, $data);
        $this->assertSame(array_slice($this->getDataSet(), 0, 5), $data);
    }

    public function testOffsetIsApplied(): void
    {
        $reader = (new ArrayDataReader($this->getDataSet()))->withOffset(2);

        $data = $reader->read();

        $this->assertCount(3, $data);
        $this->assertSame(array_slice($this->getDataSet(), 2, 3), $data);
    }

    public function testsWithSortIsImmutable(): void
    {
        $reader = new ArrayDataReader([]);

        $this->assertNotSame($reader, $reader->withSort(null));
    }

    public function testSorting(): void
    {
        $sorting = new Sort([
            'id',
            'name'
        ]);

        $sorting = $sorting->withOrder(['name' => 'asc']);

        $reader = (new ArrayDataReader($this->getDataSet()))
            ->withSort($sorting);

        $data = $reader->read();

        $this->assertSame($this->getDataSetSortedByName(), $data);
    }

    public function testCounting(): void
    {
        $reader = new ArrayDataReader($this->getDataSet());
        $this->assertSame(5, $reader->count());
        $this->assertCount(5, $reader);
    }

    public function testsWithFilterIsImmutable(): void
    {
        $reader = new ArrayDataReader([]);

        $this->assertNotSame($reader, $reader->withFilter(null));
    }

    public function testFiltering(): void
    {
        // TODO: implement
    }
}
