<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Paginator;

use ArrayIterator;
use InvalidArgumentException;
use RuntimeException;
use stdClass;
use Yiisoft\Data\Paginator\KeysetPaginator;
use Yiisoft\Data\Reader\Filter\GreaterThan;
use Yiisoft\Data\Reader\Filter\GreaterThanOrEqual;
use Yiisoft\Data\Reader\Filter\LessThan;
use Yiisoft\Data\Reader\Filter\LessThanOrEqual;
use Yiisoft\Data\Reader\FilterableDataInterface;
use Yiisoft\Data\Reader\FilterHandlerInterface;
use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\Iterable\IterableDataReader;
use Yiisoft\Data\Reader\ReadableDataInterface;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Data\Reader\SortableDataInterface;
use Yiisoft\Data\Tests\TestCase;

use function array_values;
use function count;
use function end;
use function reset;
use function sprintf;

final class KeysetPaginatorTest extends Testcase
{
    private const ITEM_1 = [
        'id' => 1,
        'name' => 'Codename Boris',
    ];
    private const ITEM_2 = [
        'id' => 2,
        'name' => 'Codename Doris',
    ];
    private const ITEM_3 = [
        'id' => 3,
        'name' => 'Agent K',
    ];
    private const ITEM_4 = [
        'id' => 5,
        'name' => 'Agent J',
    ];
    private const ITEM_5 = [
        'id' => 6,
        'name' => '007',
    ];
    private const DEFAULT_DATASET = [
        0 => self::ITEM_1,
        1 => self::ITEM_2,
        2 => self::ITEM_3,
        3 => self::ITEM_4,
        4 => self::ITEM_5,
    ];

    public function testDataReaderWithoutFilterableInterface(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Data reader should implement "%s" to be used with keyset paginator.',
            FilterableDataInterface::class,
        ));

        new KeysetPaginator($this->getNonFilterableDataReader());
    }

    public function testDataReaderWithoutSortableInterface(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Data reader should implement "%s" to be used with keyset paginator.',
            SortableDataInterface::class,
        ));

        new KeysetPaginator($this->getNonSortableDataReader());
    }

    public function testThrowsExceptionWhenReaderHasNoSort(): void
    {
        $dataReader = new IterableDataReader($this->getDataSet());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Data sorting should be configured to work with keyset pagination.');

        new KeysetPaginator($dataReader);
    }

    public function testThrowsExceptionWhenNotSorted(): void
    {
        $sort = Sort::only(['id', 'name']);
        $dataReader = (new IterableDataReader($this->getDataSet()))->withSort($sort);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Data should be always sorted to work with keyset pagination.');

        new KeysetPaginator($dataReader);
    }

    public function testPageSizeCannotBeLessThanOne(): void
    {
        $sort = Sort::only(['id', 'name'])->withOrderString('id');
        $dataReader = (new IterableDataReader($this->getDataSet()))->withSort($sort);
        $paginator = new KeysetPaginator($dataReader);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Page size should be at least 1.');

        $paginator->withPageSize(0);
    }

    public function onePageDataProvider(): array
    {
        return [
            [[], 1],
            [[], 2],
            [[], 3],

            [$this->getDataSet([0]), 1],

            [$this->getDataSet([0]), 2],
            [$this->getDataSet([0, 1]), 2],

            [$this->getDataSet([0]), 3],
            [$this->getDataSet([0, 1]), 3],
            [$this->getDataSet([0, 1, 2]), 3],

            [$this->getDataSet([0]), 4],
            [$this->getDataSet([0, 1]), 4],
            [$this->getDataSet([0, 1, 2]), 4],
            [$this->getDataSet([0, 1, 2, 3]), 4],
        ];
    }

    /**
     * @dataProvider onePageDataProvider
     */
    public function testOnePage(array $dataSet, int $pageSize): void
    {
        $sort = Sort::only(['id', 'name'])->withOrderString('id');
        $dataReader = (new IterableDataReader($dataSet))->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))->withPageSize($pageSize);

        $this->assertTrue($paginator->isOnFirstPage());
        $this->assertTrue($paginator->isOnLastPage());
    }

    public function testEmptyData(): void
    {
        $sort = Sort::only(['id', 'name'])->withOrderString('id');
        $dataReader = (new IterableDataReader([]))->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))
            ->withPageSize(1)
            ->withNextPageToken('1')
        ;

        $this->assertTrue($paginator->isOnFirstPage());
        $this->assertTrue($paginator->isOnLastPage());
        $this->assertEmpty($paginator->read());
    }

    public function testReadFirstPage(): void
    {
        $sort = Sort::only(['id', 'name'])->withOrderString('id');
        $dataReader = (new IterableDataReader($this->getDataSet()))->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))->withPageSize(2);

        $expected = $this->getDataSet([0, 1]);

        $this->assertSame($expected, $this->iterableToArray($paginator->read()));
        $last = end($expected);
        $this->assertSame((string) $last['id'], $paginator->getNextPageToken());
        $this->assertTrue($paginator->isOnFirstPage());
    }

    public function testReadObjectsWithPublicProperties(): void
    {
        $sort = Sort::only(['id', 'name'])->withOrderString('id');
        $data = [
            $this->createObjectWithPublicProperties(1, 'Codename Boris 1'),
            $this->createObjectWithPublicProperties(2, 'Codename Boris 2'),
            $this->createObjectWithPublicProperties(3, 'Codename Boris 3'),
        ];

        $dataReader = (new IterableDataReader($data))->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))->withPageSize(2);

        $this->assertSame([$data[0], $data[1]], $this->iterableToArray($paginator->read()));
        $this->assertSame((string) $data[1]->id, $paginator->getNextPageToken());
        $this->assertTrue($paginator->isOnFirstPage());
    }

    public function readObjectsWithGettersDataProvider(): array
    {
        return [
            'order by id field' => ['id', 'getId'],
            'order by created_at field' => ['created_at', 'getCreatedAt'],
        ];
    }

    /**
     * @dataProvider readObjectsWithGettersDataProvider
     */
    public function testReadObjectsWithGetters(string $orderByField, string $getter): void
    {
        $sort = Sort::only(['id', 'name', 'created_at'])->withOrderString($orderByField);
        $data = [
            $this->createObjectWithGetters(1, 'Codename Boris 1'),
            $this->createObjectWithGetters(2, 'Codename Boris 2'),
            $this->createObjectWithGetters(3, 'Codename Boris 3'),
        ];

        $dataReader = $this
            ->createObjectDataReader($data)
            ->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))->withPageSize(2);

        $this->assertSame([$data[0], $data[1]], $this->iterableToArray($paginator->read()));
        $this->assertSame((string) $data[1]->$getter(), $paginator->getNextPageToken());
        $this->assertTrue($paginator->isOnFirstPage());
    }

    public function testReadSecondPage(): void
    {
        $sort = Sort::only(['id', 'name'])->withOrderString('id');

        $dataReader = (new IterableDataReader($this->getDataSet()))
            ->withSort($sort);

        $paginator = (new KeysetPaginator($dataReader))
            ->withPageSize(2)
            ->withNextPageToken('2');

        $expected = $this->getDataSet([2, 3]);

        $this->assertSame($expected, array_values((array) $paginator->read()));
        $last = end($expected);
        $this->assertSame((string)$last['id'], $paginator->getNextPageToken());
    }

    public function testReadSecondPageOrderedByName(): void
    {
        $sort = Sort::only(['id', 'name'])->withOrderString('name');
        $dataReader = (new IterableDataReader($this->getDataSet()))->withSort($sort);

        $paginator = (new KeysetPaginator($dataReader))
            ->withPageSize(2)
            ->withNextPageToken('Agent J')
        ;

        $expected = $this->getDataSet([2, 0]);

        $this->assertSame($expected, array_values($this->iterableToArray($paginator->read())));
        $last = end($expected);
        $this->assertSame((string)$last['name'], $paginator->getNextPageToken());
    }

    public function testBackwardPagination(): void
    {
        $sort = Sort::only(['id', 'name'])->withOrderString('id');
        $dataReader = (new IterableDataReader($this->getDataSet()))->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))
            ->withPageSize(2)
            ->withPreviousPageToken('5')
        ;

        $expected = $this->getDataSet([1, 2]);
        $read = array_values($this->iterableToArray($paginator->read()));

        $this->assertSame($expected, $read);
        $first = reset($expected);
        $last = end($expected);
        $this->assertSame((string) $last['id'], $paginator->getNextPageToken(), 'Last value fail!');
        $this->assertSame((string) $first['id'], $paginator->getPreviousPageToken(), 'First value fail!');
    }

    public function testForwardAndBackwardPagination(): void
    {
        $sort = Sort::only(['id', 'name'])->withOrderString('id');
        $dataReader = (new IterableDataReader($this->getDataSet()))->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))
            ->withPageSize(2)
            ->withNextPageToken('2')
        ;

        $expected = $this->getDataSet([2, 3]);
        $read = array_values($this->iterableToArray($paginator->read()));

        $this->assertSame($expected, $read);

        $first = reset($expected);
        $last = end($expected);

        $this->assertSame((string) $last['id'], $paginator->getNextPageToken(), 'Last value fail!');
        $this->assertSame((string) $first['id'], $paginator->getPreviousPageToken(), 'First value fail!');

        $paginator = (new KeysetPaginator($dataReader))
            ->withPageSize(2)
            ->withPreviousPageToken($paginator->getPreviousPageToken());

        $expected = $this->getDataSet([0, 1]);
        $read = array_values($this->iterableToArray($paginator->read()));

        $this->assertSame($expected, $read);
        $last = end($expected);
        $this->assertSame((string) $last['id'], $paginator->getNextPageToken(), 'Last value fail!');
        $this->assertNull($paginator->getPreviousPageToken(), 'First value fail!');
    }

    public function testIsOnFirstPage(): void
    {
        $sort = Sort::only(['id'])->withOrderString('id');
        $dataReader = (new IterableDataReader($this->getDataSet()))->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))->withPageSize(2);

        $this->assertTrue($paginator->isOnFirstPage());
        $this->assertTrue($paginator->isRequired());
    }

    public function testIsOnLastPage(): void
    {
        $sort = Sort::only(['id'])->withOrderString('id');
        $dataReader = (new IterableDataReader($this->getDataSet()))->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))->withPageSize(2);

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

    public function testIsRequired(): void
    {
        $sort = Sort::only(['id'])->withOrderString('id');
        $dataReader = (new IterableDataReader($this->getDataSet()))->withSort($sort);
        $paginator = new KeysetPaginator($dataReader);

        $this->assertFalse($paginator->isRequired());

        $paginator = $paginator->withPageSize(2);

        $this->assertTrue($paginator->isRequired());
    }

    public function testCurrentPageSize(): void
    {
        $sort = Sort::only(['id'])->withOrderString('id');
        $dataReader = (new IterableDataReader($this->getDataSet()))->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))->withPageSize(2);
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
        $sort = Sort::only(['id'])->withOrderString('id');
        $dataSet = new class ($this->getDataSet()) extends ArrayIterator {
            private int $rewindCounter = 0;

            public function rewind(): void
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
        $paginator = (new KeysetPaginator($dataReader))->withPageSize(2);
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
        $sort = Sort::only(['id'])->withOrderString('id');
        $dataReader = (new IterableDataReader($this->getDataSet()))->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))->withPageSize(2);
        $this->assertNotNull($paginator->getNextPageToken());

        $paginator = $paginator->withPreviousPageToken('1');
        try {
            $paginator->getNextPageToken();
            $this->fail();
        } catch (RuntimeException) {
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
            $this->fail();
        } catch (RuntimeException) {
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
        $sort = Sort::only(['id'])->withOrderString('id');
        $dataReader = (new IterableDataReader($this->getDataSet()))->withSort($sort);
        $paginator = new KeysetPaginator($dataReader);

        $this->assertSame(10, $paginator->getPageSize());
        $this->assertCount(5, $paginator->read());
    }

    public function testCustomPageSize(): void
    {
        $sort = Sort::only(['id'])->withOrderString('id');
        $dataReader = (new IterableDataReader($this->getDataSet()))->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))->withPageSize(2);

        $this->assertSame(2, $paginator->getPageSize());
        $this->assertCount(2, $paginator->read());
    }

    private function getDataSet(array $keys = null): array
    {
        if ($keys === null) {
            return self::DEFAULT_DATASET;
        }

        $result = [];

        foreach ($keys as $key) {
            $result[] = self::DEFAULT_DATASET[$key];
        }

        return $result;
    }

    private function getNonSortableDataReader()
    {
        return new class () implements ReadableDataInterface, FilterableDataInterface {
            public function withLimit(int $limit): static
            {
                return clone $this;
            }

            public function read(): iterable
            {
                return [];
            }

            public function readOne(): array|object|null
            {
                return null;
            }

            public function withFilter(FilterInterface $filter): static
            {
                return clone $this;
            }

            public function withFilterHandlers(FilterHandlerInterface ...$iterableFilterUnits): static
            {
                return clone $this;
            }
        };
    }

    private function getNonFilterableDataReader()
    {
        return new class () implements ReadableDataInterface, SortableDataInterface {
            public function withLimit(int $limit): static
            {
                return clone $this;
            }

            public function read(): iterable
            {
                return [];
            }

            public function readOne(): array|object|null
            {
                return null;
            }

            public function withSort(?Sort $sort): static
            {
                return clone $this;
            }

            public function getSort(): ?Sort
            {
                return Sort::only([]);
            }
        };
    }

    private function createObjectWithPublicProperties(int $id, string $name): stdClass
    {
        $object = new stdClass();
        $object->id = $id;
        $object->name = $name;

        return $object;
    }

    private function createObjectWithGetters(int $id, string $name): object
    {
        return new class ($id, $name) {
            private int $createdAt;

            public function __construct(private int $id, private string $name, int $createdAt = null)
            {
                $this->createdAt = $createdAt ?: time();
            }

            public function getId(): string
            {
                return (string) $this->id;
            }

            public function getName(): string
            {
                return $this->name;
            }

            public function getCreatedAt(): int
            {
                return $this->createdAt;
            }
        };
    }

    private function createObjectDataReader(array $data): IterableDataReader
    {
        return new class ($data) extends IterableDataReader {
            public function read(): array
            {
                $data = [];

                foreach ($this->data as $item) {
                    if (count($data) === 3) {
                        break;
                    }

                    $data[] = $item;
                }

                return $data;
            }
        };
    }

    public function testGetSort(): void
    {
        $sort = Sort::only(['id'])->withOrderString('id');
        $dataReader = (new IterableDataReader($this->getDataSet()))->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader));

        $this->assertInstanceOf(Sort::class, $paginator->getSort());
    }

    public function testWithPreviousPageTokenAndIsOnFirstPageSameTrue(): void
    {
        $sort = Sort::only(['id'])->withOrderString('id');
        $dataReader = (new IterableDataReader($this->getDataSet()))->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))->withPreviousPageToken('1');

        $this->assertTrue($paginator->isOnFirstPage());
    }

    public function testCloneClearValues(): void
    {
        $sort = Sort::only(['id'])->withOrderString('id');
        $dataReader = (new IterableDataReader($this->getDataSet()))->withSort($sort);
        $paginator = new KeysetPaginator($dataReader);
        $paginator->read();

        $this->assertSame($this->getDataSet(), $this->getInaccessibleProperty($paginator, 'readCache'));
        $this->assertSame('1', $this->getInaccessibleProperty($paginator, 'currentFirstValue'));
        $this->assertSame('6', $this->getInaccessibleProperty($paginator, 'currentLastValue'));

        $paginator = $paginator->withPreviousPageToken('1');
        $paginator->read();
        $this->assertTrue($this->getInaccessibleProperty($paginator, 'hasNextPage'));

        $paginator = $paginator->withNextPageToken('3');
        $paginator->read();
        $this->assertTrue($this->getInaccessibleProperty($paginator, 'hasPreviousPage'));

        $paginator = clone $paginator;

        $this->assertNull($this->getInaccessibleProperty($paginator, 'readCache'));
        $this->assertNull($this->getInaccessibleProperty($paginator, 'currentFirstValue'));
        $this->assertNull($this->getInaccessibleProperty($paginator, 'currentLastValue'));
        $this->assertFalse($this->getInaccessibleProperty($paginator, 'hasNextPage'));
        $this->assertFalse($this->getInaccessibleProperty($paginator, 'hasPreviousPage'));
    }

    public function testImmutability(): void
    {
        $sort = Sort::only(['id'])->withOrderString('id');
        $dataReader = (new IterableDataReader($this->getDataSet()))->withSort($sort);
        $paginator = new KeysetPaginator($dataReader);

        $this->assertNotSame($paginator, $paginator->withNextPageToken('1'));
        $this->assertNotSame($paginator, $paginator->withPreviousPageToken('1'));
        $this->assertNotSame($paginator, $paginator->withPageSize(1));
    }

    public function testGetPreviousPageExistForCoverage(): void
    {
        $sort = Sort::only(['id'])->withOrderString('id');
        $dataReader = (new IterableDataReader($this->getDataSet()))->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))->withNextPageToken('1');


        $this->assertTrue($this->invokeMethod($paginator, 'previousPageExist', [$dataReader, $sort]));
    }

    public function testGetFilterForCoverage(): void
    {
        $sort = Sort::only(['id'])->withOrderString('id');
        $dataReader = (new IterableDataReader([]))->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))->withNextPageToken('1');

        $this->assertInstanceOf(
            GreaterThan::class,
            $this->invokeMethod($paginator, 'getFilter', [$sort]),
        );

        $sort = Sort::only(['id'])
            ->withOrderString('id')
            ->withOrder(['id' => 'desc']);
        $dataReader = (new IterableDataReader([]))->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))->withNextPageToken('1');

        $this->assertInstanceOf(
            LessThan::class,
            $this->invokeMethod($paginator, 'getFilter', [$sort]),
        );
    }

    public function testGetReverseFilterForCoverage(): void
    {
        $sort = Sort::only(['42'])->withOrderString('42');
        $dataReader = (new IterableDataReader([]))->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))->withNextPageToken('1');

        $this->assertInstanceOf(
            LessThanOrEqual::class,
            $this->invokeMethod($paginator, 'getReverseFilter', [$sort]),
        );

        $sort = Sort::only(['42'])
            ->withOrderString('42')
            ->withOrder(['42' => 'desc']);
        $dataReader = (new IterableDataReader([]))->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))->withNextPageToken('1');

        $this->assertInstanceOf(
            GreaterThanOrEqual::class,
            $this->invokeMethod($paginator, 'getReverseFilter', [$sort]),
        );
    }
}
