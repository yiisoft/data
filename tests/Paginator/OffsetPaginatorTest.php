<?php
declare(strict_types=1);

namespace Yiisoft\Data\Tests\Paginator;

use Yiisoft\Data\Paginator\OffsetPaginator;
use Yiisoft\Data\Reader\ArrayDataReader;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Tests\TestCase;

final class OffsetPaginatorTest extends TestCase
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

    public function testDataReaderWithoutOffsetableInterface(): void
    {
        $nonOffsetableDataReader = new class implements DataReaderInterface
        {
            public function withLimit(int $limit)
            {
                // do nothing
            }

            public function read(): iterable
            {
                return [];
            }
        };

        $this->expectException(\InvalidArgumentException::class);

        new OffsetPaginator($nonOffsetableDataReader);
    }

    public function testCurrentPageCannotBeLessThanOne(): void
    {
        $dataReader = new ArrayDataReader($this->getDataSet());
        $paginator = new OffsetPaginator($dataReader);

        $this->expectException(\InvalidArgumentException::class);
        $paginator->withCurrentPage(0);
    }

    public function testPageSizeCannotBeLessThanOne(): void
    {
        $dataReader = new ArrayDataReader($this->getDataSet());
        $paginator = new OffsetPaginator($dataReader);

        $this->expectException(\InvalidArgumentException::class);
        $paginator->withPageSize(0);
    }

    public function testReadFirstPage(): void
    {
        $dataReader = new ArrayDataReader($this->getDataSet());

        $paginator = (new OffsetPaginator($dataReader))
            ->withPageSize(2)
            ->withCurrentPage(1);

        $expected = [
            [
                'id' => 1,
                'name' => 'Codename Boris',
            ],
            [
                'id' => 2,
                'name' => 'Codename Doris',
            ],
        ];

        $this->assertSame($expected, $this->iterableToArray($paginator->read()));
    }

    public function testReadSecondPage(): void
    {
        $dataReader = new ArrayDataReader($this->getDataSet());

        $paginator = (new OffsetPaginator($dataReader))
            ->withPageSize(2)
            ->withCurrentPage(2);

        $expected = [
            [
                'id' => 3,
                'name' => 'Agent K',
            ],
            [
                'id' => 5,
                'name' => 'Agent J',
            ],
        ];

        $this->assertSame($expected, $this->iterableToArray($paginator->read()));
    }

    public function testReadLastPage(): void
    {
        $dataReader = new ArrayDataReader($this->getDataSet());

        $paginator = (new OffsetPaginator($dataReader))
            ->withPageSize(2)
            ->withCurrentPage(3);

        $expected = [
            [
                'id' => 6,
                'name' => '007',
            ],
        ];

        $this->assertSame($expected, $this->iterableToArray($paginator->read()));
    }

    public function testTotalPages(): void
    {
        $dataReader = new ArrayDataReader($this->getDataSet());
        $paginator = (new OffsetPaginator($dataReader))
            ->withPageSize(2);

        $this->assertSame(3, $paginator->getTotalPages());
    }

    public function testIsFirstPageOnFirstPage(): void
    {
        $dataReader = new ArrayDataReader($this->getDataSet());
        $paginator = (new OffsetPaginator($dataReader))
            ->withPageSize(2)
            ->withCurrentPage(1);

        $this->assertTrue($paginator->isOnFirstPage());
    }

    public function testIsFirstPageOnSecondPage(): void
    {
        $dataReader = new ArrayDataReader($this->getDataSet());
        $paginator = (new OffsetPaginator($dataReader))
            ->withPageSize(2)
            ->withCurrentPage(2);

        $this->assertFalse($paginator->isOnFirstPage());
    }

    public function testIsLastPageOnFirstPage(): void
    {
        $dataReader = new ArrayDataReader($this->getDataSet());
        $paginator = (new OffsetPaginator($dataReader))
            ->withPageSize(2)
            ->withCurrentPage(1);

        $this->assertFalse($paginator->isOnLastPage());
    }

    public function testIsLastPageOnLastPage(): void
    {
        $dataReader = new ArrayDataReader($this->getDataSet());
        $paginator = (new OffsetPaginator($dataReader))
            ->withPageSize(2)
            ->withCurrentPage(3);

        $this->assertTrue($paginator->isOnLastPage());
    }
}
