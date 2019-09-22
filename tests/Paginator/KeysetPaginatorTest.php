<?php
declare(strict_types=1);

namespace Yiisoft\Data\Tests\Paginator;

use Yiisoft\Data\Paginator\KeysetPaginator;
use Yiisoft\Data\Paginator\PaginatorInterface;
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
            ->withNextPageToken("3");

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
        $this->assertSame((string)$last['id'], $paginator->getNextPageToken());
        $this->assertSame(true, $paginator->isOnFirstPage());
    }

    public function testReadSecondPage(): void
    {
        $sort = (new Sort(['id', 'name']))->withOrderString('id');

        $dataReader = (new IterableDataReader($this->getDataSet()))
            ->withSort($sort);

        $paginator = (new KeysetPaginator($dataReader))
            ->withPageSize(2)
            ->withNextPageToken("2");

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
        $this->assertSame((string)$last['id'], $paginator->getNextPageToken());
    }

    public function testReadSecondPageOrderedByName(): void
    {
        $sort = (new Sort(['id', 'name']))->withOrderString('name');

        $dataReader = (new IterableDataReader($this->getDataSet()))
            ->withSort($sort);

        $paginator = (new KeysetPaginator($dataReader))
            ->withPageSize(2)
            ->withNextPageToken('Agent J');

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
        $this->assertSame((string)$last['name'], $paginator->getNextPageToken());
    }

    public function testBackwardPagination(): void
    {
        $sort = (new Sort(['id', 'name']))->withOrderString('id');

        $dataReader = (new IterableDataReader($this->getDataSet()))
            ->withSort($sort);

        $paginator = (new KeysetPaginator($dataReader))
            ->withPageSize(2)
            ->withPreviousPageToken("5");

        $expected = [
            [
                'id' => 2,
                'name' => 'Codename Doris',
            ],
            [
                'id' => 3,
                'name' => 'Agent K',
            ],
        ];
        $this->assertSame($expected, $this->iterableToArray($paginator->read()));
        $first = reset($expected);
        $last = end($expected);
        $this->assertSame((string)$last['id'], $paginator->getNextPageToken(), 'Last value fail!');
        $this->assertSame((string)$first['id'], $paginator->getPreviousPageToken(), 'First value fail!');
    }

    public function testForwardAndBackwardPagination(): void
    {
        $sort = (new Sort(['id', 'name']))->withOrderString('id');

        $dataReader = (new IterableDataReader($this->getDataSet()))
            ->withSort($sort);

        $paginator = (new KeysetPaginator($dataReader))
            ->withPageSize(2)
            ->withNextPageToken("2");

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
        $first = reset($expected);
        $last = end($expected);
        $this->assertSame((string)$last['id'], $paginator->getNextPageToken(), 'Last value fail!');
        $this->assertSame((string)$first['id'], $paginator->getPreviousPageToken(), 'First value fail!');

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

        $paginator = (new KeysetPaginator($dataReader))
            ->withPageSize(2)
            ->withPreviousPageToken($paginator->getPreviousPageToken());

        $this->assertSame($expected, $this->iterableToArray($paginator->read()));
        $first = reset($expected);
        $last = end($expected);
        $this->assertSame((string)$last['id'], $paginator->getNextPageToken(), 'Last value fail!');
        $this->assertSame((string)$first['id'], $paginator->getPreviousPageToken(), 'First value fail!');
    }

    public function testIsOnFirstPage(): void
    {
        $sort = (new Sort(['id']))->withOrderString('id');
        $dataReader = (new IterableDataReader($this->getDataSet()))
            ->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))
            ->withPageSize(2);
        $this->assertSame(true, $paginator->isOnFirstPage());
    }

    public function testIsOnLastPage(): void
    {
        $sort = (new Sort(['id']))->withOrderString('id');
        $dataReader = (new IterableDataReader($this->getDataSet()))
            ->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))
            ->withPageSize(2);

        try {
            $paginator->isOnLastPage();
            $this->assertTrue(false);
        } catch (\RuntimeException $e) {
            $this->assertTrue(true);
        }
        $paginator = $paginator->withNextPageToken("6");
        $this->assertSame(true, $paginator->isOnLastPage());
        $paginator = $paginator->withNextPageToken("5");
        $this->assertSame(true, $paginator->isOnLastPage());
        $paginator = $paginator->withNextPageToken("4");
        $this->assertSame(false, $paginator->isOnLastPage());
    }

    public function testCurrentPageSize(): void
    {
        $sort = (new Sort(['id']))->withOrderString('id');
        $dataReader = (new IterableDataReader($this->getDataSet()))
            ->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))
            ->withPageSize(2);
        $this->assertSame(2, $paginator->getCurrentPageSize());
        $paginator = $paginator->withPreviousPageToken("1");
        $this->assertSame(0, $paginator->getCurrentPageSize());
        $paginator = $paginator->withPreviousPageToken("2");
        $this->assertSame(1, $paginator->getCurrentPageSize());
        $paginator = $paginator->withPreviousPageToken("3");
        $this->assertSame(2, $paginator->getCurrentPageSize());

        $paginator = $paginator->withNextPageToken("6");
        $this->assertSame(0, $paginator->getCurrentPageSize());
        $paginator = $paginator->withNextPageToken("5");
        $this->assertSame(1, $paginator->getCurrentPageSize());
        $paginator = $paginator->withNextPageToken("4");
        $this->assertSame(2, $paginator->getCurrentPageSize());
    }

    public function testReadCache()
    {
        $sort = (new Sort(['id']))->withOrderString('id');
        $dataSet = new class($this->getDataSet()) extends \ArrayIterator
        {
            private $rewindCounter = 0;

            public function rewind()
            {
                $this->rewindCounter++;
                parent::rewind();
            }

            public function getRewindCounter(): int
            {
                return $this->rewindCounter;
            }
        };
        $dataReader = (new IterableDataReader($dataSet))->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))
            ->withPageSize(2);
        // not use datase test
        $this->assertSame(0, $dataSet->getRewindCounter());
        // first use dataset test
        $paginator->getCurrentPageSize();
        $this->assertSame(1, $dataSet->getRewindCounter());
        // repeated use dataset test
        $paginator->getCurrentPageSize();
        $this->assertSame(1, $dataSet->getRewindCounter());
        $paginator->isOnFirstPage();
        $this->assertSame(1, $dataSet->getRewindCounter());
        $paginator->isOnLastPage();
        $this->assertSame(1, $dataSet->getRewindCounter());
        foreach($paginator->read() as $void);
        $this->assertSame(1, $dataSet->getRewindCounter());
        // clear cache test
        $paginator = (new KeysetPaginator($dataReader))
            ->withPageSize(3);
        $this->assertSame(1, $dataSet->getRewindCounter());
        // recreate cache
        $paginator->getCurrentPageSize();
        $this->assertSame(2, $dataSet->getRewindCounter());
        $paginator->getCurrentPageSize();
        $this->assertSame(2, $dataSet->getRewindCounter());
    }

}
