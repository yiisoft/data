<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Paginator;

use Yiisoft\Data\Paginator\KeysetPaginator;
use Yiisoft\Data\Reader\Filter\FilterProcessorInterface;
use Yiisoft\Data\Reader\Iterable\IterableDataReader;
use Yiisoft\Data\Reader\Filter\FilterInterface;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\FilterableDataInterface;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Data\Reader\SortableDataInterface;
use Yiisoft\Data\Tests\TestCase;

final class KeysetPaginatorTest extends Testcase
{
    public function testDataReaderWithoutFilterableInterface(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Data reader should implement FilterableDataInterface to be used with keyset paginator.'
        );

        new KeysetPaginator($this->getNonFilterableDataReader());
    }

    public function testDataReaderWithoutSortableInterface(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Data reader should implement SortableDataInterface to be used with keyset paginator.'
        );

        new KeysetPaginator($this->getNonSortableDataReader());
    }

    public function testPageSizeCannotBeLessThanOne(): void
    {
        $sort = (new Sort(['id', 'name']))->withOrderString('id');
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

        $this->expectException(\RuntimeException::class);

        new KeysetPaginator($dataReader);
    }

    /**
     * @dataProvider onePageDataProvider
     */
    public function testOnePage(array $dataSet, int $pageSize): void
    {
        $sort = (new Sort(['id', 'name']))->withOrderString('id');

        $dataReader = (new IterableDataReader($dataSet))
            ->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))
            ->withPageSize($pageSize);
        $this->assertTrue($paginator->isOnFirstPage());
        $this->assertTrue($paginator->isOnLastPage());
    }

    public function testEmptyData(): void
    {
        $sort = (new Sort(['id', 'name']))->withOrderString('id');
        $dataReader = (new IterableDataReader([]))
            ->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))
            ->withPageSize(1)
            ->withNextPageToken('1');

        $this->assertTrue($paginator->isOnFirstPage());
        $this->assertTrue($paginator->isOnLastPage());
        $this->assertEmpty($paginator->read());
    }

    public function onePageDataProvider(): array
    {
        return [
            [[], 1],
            [[], 2],
            [[], 3],

            [array_slice($this->getDataSet(), 0, 1), 1],

            [array_slice($this->getDataSet(), 0, 1), 2],
            [array_slice($this->getDataSet(), 0, 2), 2],

            [array_slice($this->getDataSet(), 0, 1), 3],
            [array_slice($this->getDataSet(), 0, 2), 3],
            [array_slice($this->getDataSet(), 0, 3), 3],

            [array_slice($this->getDataSet(), 0, 1), 4],
            [array_slice($this->getDataSet(), 0, 2), 4],
            [array_slice($this->getDataSet(), 0, 3), 4],
            [array_slice($this->getDataSet(), 0, 4), 4],
        ];
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
        $this->assertTrue($paginator->isOnFirstPage());
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
            ->withPreviousPageToken('5');

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
        $last = end($expected);
        $this->assertSame((string)$last['id'], $paginator->getNextPageToken(), 'Last value fail!');
        $this->assertNull($paginator->getPreviousPageToken(), 'First value fail!');
    }

    public function testIsOnFirstPage(): void
    {
        $sort = (new Sort(['id']))->withOrderString('id');
        $dataReader = (new IterableDataReader($this->getDataSet()))
            ->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))
            ->withPageSize(2);
        $this->assertTrue($paginator->isOnFirstPage());
        $this->assertTrue($paginator->isRequired());
    }

    public function testIsOnLastPage(): void
    {
        $sort = (new Sort(['id']))->withOrderString('id');
        $dataReader = (new IterableDataReader($this->getDataSet()))
            ->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))
            ->withPageSize(2);

        $paginator = $paginator->withNextPageToken('6');
        $this->assertTrue($paginator->isOnLastPage());
        $paginator = $paginator->withNextPageToken('5');
        $this->assertTrue($paginator->isOnLastPage());
        $paginator = $paginator->withNextPageToken('4');
        $this->assertTrue($paginator->isOnLastPage());
        $paginator = $paginator->withNextPageToken('3');
        $this->assertTrue($paginator->isOnLastPage());
        $paginator = $paginator->withNextPageToken('2');
        $this->assertFalse($paginator->isOnLastPage());
        $this->assertTrue($paginator->isRequired());
    }

    public function testCurrentPageSize(): void
    {
        $sort = (new Sort(['id']))->withOrderString('id');
        $dataReader = (new IterableDataReader($this->getDataSet()))
            ->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))
            ->withPageSize(2);
        $this->assertSame(2, $paginator->getCurrentPageSize());
        $paginator = $paginator->withPreviousPageToken('1');
        $this->assertSame(0, $paginator->getCurrentPageSize());
        $paginator = $paginator->withPreviousPageToken('2');
        $this->assertSame(1, $paginator->getCurrentPageSize());
        $paginator = $paginator->withPreviousPageToken('3');
        $this->assertSame(2, $paginator->getCurrentPageSize());

        $paginator = $paginator->withNextPageToken('6');
        $this->assertSame(0, $paginator->getCurrentPageSize());
        $paginator = $paginator->withNextPageToken('5');
        $this->assertSame(1, $paginator->getCurrentPageSize());
        $paginator = $paginator->withNextPageToken('4');
        $this->assertSame(2, $paginator->getCurrentPageSize());
    }

    public function testReadCache(): void
    {
        $sort = (new Sort(['id']))->withOrderString('id');
        $dataSet = new class($this->getDataSet()) extends \ArrayIterator {
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
        foreach ($paginator->read() as $void) {
        }
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

    public function testTokenResults(): void
    {
        $sort = (new Sort(['id']))->withOrderString('id');
        $dataReader = (new IterableDataReader($this->getDataSet()))
            ->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))
            ->withPageSize(2);
        $this->assertNotNull($paginator->getNextPageToken());
        $paginator = $paginator->withPreviousPageToken('1');
        try {
            $paginator->getNextPageToken();
            $this->assertTrue(false);
        } catch (\RuntimeException $e) {
            $this->assertTrue(true);
        }
        $this->assertNull($paginator->getPreviousPageToken());
        $paginator = $paginator->withPreviousPageToken('2');
        $this->assertNotNull($paginator->getNextPageToken());
        $this->assertSame('1', $paginator->getNextPageToken());
        $this->assertNull($paginator->getPreviousPageToken());
        $paginator = $paginator->withPreviousPageToken('3');
        $this->assertNotNull($paginator->getNextPageToken());
        $this->assertSame('2', $paginator->getNextPageToken());
        $this->assertNull($paginator->getPreviousPageToken());

        $paginator = $paginator->withNextPageToken('6');
        try {
            $paginator->getPreviousPageToken();
            $this->assertTrue(false);
        } catch (\RuntimeException $e) {
            $this->assertTrue(true);
        }
        $this->assertNull($paginator->getNextPageToken());
        $paginator = $paginator->withNextPageToken('5');
        $this->assertNotNull($paginator->getPreviousPageToken());
        $this->assertSame('6', $paginator->getPreviousPageToken());
        $this->assertNull($paginator->getNextPageToken());
        $paginator = $paginator->withNextPageToken('4');
        $this->assertNull($paginator->getNextPageToken());
        $this->assertNotNull($paginator->getPreviousPageToken());
        $this->assertSame('5', $paginator->getPreviousPageToken());
    }

    public function testDefaultPageSize(): void
    {
        $sort = (new Sort(['id']))->withOrderString('id');
        $dataReader = (new IterableDataReader($this->getDataSet()))->withSort($sort);
        $paginator = new KeysetPaginator($dataReader);
        $this->assertSame(10, $paginator->getPageSize());
        $this->assertCount(5, $paginator->read());
    }

    public function testCustomPageSize(): void
    {
        $sort = (new Sort(['id']))->withOrderString('id');
        $dataReader = (new IterableDataReader($this->getDataSet()))->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))->withPageSize(2);
        $this->assertSame(2, $paginator->getPageSize());
        $this->assertCount(2, $paginator->read());
    }

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

    private function getNonSortableDataReader()
    {
        return new class() implements DataReaderInterface, FilterableDataInterface {
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

            public function withFilterProcessors(FilterProcessorInterface ...$filterUnits)
            {
                // do nothing
            }
        };
    }

    private function getNonFilterableDataReader()
    {
        return new class() implements DataReaderInterface, SortableDataInterface {
            public function withLimit(int $limit)
            {
                // do nothing
            }

            public function read(): iterable
            {
                return [];
            }

            public function withSort(?Sort $sorting)
            {
                // do nothing
            }

            public function getSort(): ?Sort
            {
                return new Sort([]);
            }
        };
    }
}
