<?php
declare(strict_types=1);

namespace Yiisoft\Data\Tests\Paginator;

use Yiisoft\Data\Paginator\KeysetPaginator;
use Yiisoft\Data\Reader\IterableDataReader;
use Yiisoft\Data\Reader\Filter\FilterInterface;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\FilterableDataInterface;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Data\Tests\TestCase;

final class KeysetPaginatorTest extends Testcase
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

    public function testDataReaderWithoutFilterableInterface(): void
    {
        $nonFilterableDataReader = new class implements DataReaderInterface
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

        new KeysetPaginator($nonFilterableDataReader);
    }

    public function testDataReaderWithoutSortableInterface(): void
    {
        $nonSortableDataReader = new class implements DataReaderInterface, FilterableDataInterface
        {
            public function withLimit(int $limit)
            {
                // do nothing
            }

            public function read(): iterable
            {
                return [];
            }

            public function withFilter(FilterInterface $filter)
            {
                // do nothing
            }
        };

        $this->expectException(\InvalidArgumentException::class);

        new KeysetPaginator($nonSortableDataReader);
    }

    public function testPageSizeCannotBeLessThanOne(): void
    {
        $sort = new Sort(['id', 'name']);
        $dataReader = (new IterableDataReader($this->getDataSet()))
            ->withSort($sort);
        $paginator = new KeysetPaginator($dataReader);

        $this->expectException(\InvalidArgumentException::class);
        $paginator->withPageSize(0);
    }

    public function testThrowsExceptionWhenReaderHasNoSort(): void
    {
        $dataReader = new IterableDataReader($this->getDataSet());

        $this->expectException(\RuntimeException::class);

        new KeysetPaginator($dataReader);
    }

    public function testThrowsExceptionWhenNotSorted(): void
    {
        $sort = new Sort(['id', 'name']);

        $dataReader = (new IterableDataReader($this->getDataSet()))
            ->withSort($sort);

        $paginator = (new KeysetPaginator($dataReader))
            ->withPageSize(2)
            ->withLast(3);

        $this->expectException(\RuntimeException::class);

        $this->iterableToArray($paginator->read());
    }

    public function testReadFirstPage(): void
    {
        $sort = (new Sort(['id', 'name']))->withOrderString('id');

        $dataReader = (new IterableDataReader($this->getDataSet()))
            ->withSort($sort);


        $paginator = (new KeysetPaginator($dataReader))
            ->withPageSize(2);

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
        $last = end($expected);
        $this->assertSame($last['id'], $paginator->getLastValue());
    }

    public function testReadSecondPage(): void
    {
        $sort = (new Sort(['id', 'name']))->withOrderString('id');

        $dataReader = (new IterableDataReader($this->getDataSet()))
            ->withSort($sort);

        $paginator = (new KeysetPaginator($dataReader))
            ->withPageSize(2)
            ->withLast(2);

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
        $last = end($expected);
        $this->assertSame($last['id'], $paginator->getLastValue());
    }

    public function testReadSecondPageOrderedByName(): void
    {
        $sort = (new Sort(['id', 'name']))->withOrderString('name');

        $dataReader = (new IterableDataReader($this->getDataSet()))
            ->withSort($sort);

        $paginator = (new KeysetPaginator($dataReader))
            ->withPageSize(2)
            ->withLast( 'Agent J');

        $expected = [
            [
                'id' => 3,
                'name' => 'Agent K',
            ],
            [
                'id' => 1,
                'name' => 'Codename Boris',
            ],
        ];

        $this->assertSame($expected, $this->iterableToArray($paginator->read()));
        $last = end($expected);
        $this->assertSame($last['name'], $paginator->getLastValue());
    }
}
