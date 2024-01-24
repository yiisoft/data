<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Paginator;

use ArrayIterator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use stdClass;
use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Data\Paginator\KeysetFilterContext;
use Yiisoft\Data\Paginator\KeysetPaginator;
use Yiisoft\Data\Paginator\PageToken;
use Yiisoft\Data\Reader\Filter\GreaterThan;
use Yiisoft\Data\Reader\Filter\GreaterThanOrEqual;
use Yiisoft\Data\Reader\Filter\LessThan;
use Yiisoft\Data\Reader\Filter\LessThanOrEqual;
use Yiisoft\Data\Reader\FilterableDataInterface;
use Yiisoft\Data\Reader\FilterHandlerInterface;
use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\Iterable\IterableDataReader;
use Yiisoft\Data\Reader\LimitableDataInterface;
use Yiisoft\Data\Reader\ReadableDataInterface;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Data\Reader\SortableDataInterface;
use Yiisoft\Data\Tests\Support\MutationDataReader;
use Yiisoft\Data\Tests\TestCase;

use function array_values;
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

    public function testDataReaderWithoutLimitableInterface(): void
    {
        $dataReader = new class () implements ReadableDataInterface, SortableDataInterface, FilterableDataInterface {
            public function withSort(?Sort $sort): static
            {
                return clone $this;
            }

            public function getSort(): ?Sort
            {
                return Sort::only([]);
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

            public function withFilterHandlers(FilterHandlerInterface ...$filterHandlers): static
            {
                return clone $this;
            }
        };

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Data reader should implement "%s" to be used with keyset paginator.',
            LimitableDataInterface::class,
        ));

        new KeysetPaginator($dataReader);
    }

    public function testThrowsExceptionWhenReaderHasNoSort(): void
    {
        $dataReader = new IterableDataReader(self::getDataSet());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Data sorting should be configured to work with keyset pagination.');

        new KeysetPaginator($dataReader);
    }

    public function testThrowsExceptionWhenNotSorted(): void
    {
        $sort = Sort::only(['id', 'name']);
        $dataReader = (new IterableDataReader(self::getDataSet()))->withSort($sort);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Data should be always sorted to work with keyset pagination.');

        new KeysetPaginator($dataReader);
    }

    public function testPageSizeCannotBeLessThanOne(): void
    {
        $sort = Sort::only(['id', 'name'])->withOrderString('id');
        $dataReader = (new IterableDataReader(self::getDataSet()))->withSort($sort);
        $paginator = new KeysetPaginator($dataReader);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Page size should be at least 1.');

        $paginator->withPageSize(0);
    }

    public static function dataOnePage(): array
    {
        return [
            [[], 1],
            [[], 2],
            [[], 3],

            [self::getDataSet([0]), 1],

            [self::getDataSet([0]), 2],
            [self::getDataSet([0, 1]), 2],

            [self::getDataSet([0]), 3],
            [self::getDataSet([0, 1]), 3],
            [self::getDataSet([0, 1, 2]), 3],

            [self::getDataSet([0]), 4],
            [self::getDataSet([0, 1]), 4],
            [self::getDataSet([0, 1, 2]), 4],
            [self::getDataSet([0, 1, 2, 3]), 4],
        ];
    }

    #[DataProvider('dataOnePage')]
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
        $paginator = (new KeysetPaginator($dataReader))->withPageSize(1);

        $this->assertTrue($paginator->isOnFirstPage());
        $this->assertTrue($paginator->isOnLastPage());
        $this->assertEmpty($paginator->read());
    }

    public function testReadFirstPage(): void
    {
        $sort = Sort::only(['id', 'name'])->withOrderString('id');
        $dataReader = (new IterableDataReader(self::getDataSet()))->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))->withPageSize(2);

        $expected = self::getDataSet([0, 1]);

        $this->assertSame($expected, $this->iterableToArray($paginator->read()));
        $last = end($expected);
        $this->assertSame((string)$last['id'], $paginator->getNextPageTokenValue());
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
        $this->assertSame((string)$data[1]->id, $paginator->getNextPageTokenValue());
        $this->assertTrue($paginator->isOnFirstPage());
    }

    public static function dataReadObjectsWithGetters(): array
    {
        return [
            'order by id field' => ['getId()', 'getId'],
            'order by created_at field' => ['getCreatedAt()', 'getCreatedAt'],
        ];
    }

    #[DataProvider('dataReadObjectsWithGetters')]
    public function testReadObjectsWithGetters(string $orderByField, string $getter): void
    {
        $sort = Sort::only(['getId()', 'getName()', 'getCreatedAt()'])->withOrderString($orderByField);
        $data = [
            $this->createObjectWithGetters(1, 'Codename Boris 1'),
            $this->createObjectWithGetters(2, 'Codename Boris 2'),
            $this->createObjectWithGetters(3, 'Codename Boris 3'),
        ];

        $dataReader = (new IterableDataReader($data))->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))->withPageSize(2);

        $this->assertSame([$data[0], $data[1]], $this->iterableToArray($paginator->read()));
        $this->assertSame((string)$data[1]->$getter(), $paginator->getNextPageTokenValue());
        $this->assertTrue($paginator->isOnFirstPage());
    }

    public function testReadSecondPage(): void
    {
        $sort = Sort::only(['id', 'name'])->withOrderString('id');

        $dataReader = (new IterableDataReader(self::getDataSet()))
            ->withSort($sort);

        $paginator = (new KeysetPaginator($dataReader))
            ->withPageSize(2)
            ->withPageToken(new PageToken('2', false));

        $expected = self::getDataSet([2, 3]);

        $this->assertSame($expected, array_values((array) $paginator->read()));
        $last = end($expected);
        $this->assertSame((string)$last['id'], $paginator->getNextPageTokenValue());
    }

    public function testReadSecondPageOrderedByName(): void
    {
        $sort = Sort::only(['id', 'name'])->withOrderString('name');
        $dataReader = (new IterableDataReader(self::getDataSet()))->withSort($sort);

        $paginator = (new KeysetPaginator($dataReader))
            ->withPageSize(2)
            ->withPageToken(PageToken::next('Agent J'));

        $expected = self::getDataSet([2, 0]);

        $this->assertSame($expected, array_values($this->iterableToArray($paginator->read())));
        $last = end($expected);
        $this->assertSame((string)$last['name'], $paginator->getNextPageTokenValue());
    }

    public static function dataReadOne(): array
    {
        $data = [];

        $data['empty'] = [
            null,
            new KeysetPaginator(
                (new IterableDataReader([]))->withSort(Sort::only(['id'])->withOrderString('id'))
            ),
        ];

        $data['base'] = [
            ['id' => 2, 'name' => 'John'],
            new KeysetPaginator(
                (new IterableDataReader([
                    ['id' => 1, 'name' => 'Mike'],
                    ['id' => 2, 'name' => 'John'],
                ]))
                    ->withSort(Sort::only(['id'])->withOrderString('-id'))
            ),
        ];

        return $data;
    }

    #[DataProvider('dataReadOne')]
    public function testReadOne(mixed $expected, KeysetPaginator $paginator): void
    {
        $result = $paginator->readOne();
        $this->assertSame($expected, $result);
    }

    public function testBackwardPagination(): void
    {
        $sort = Sort::only(['id', 'name'])->withOrderString('id');
        $dataReader = (new IterableDataReader(self::getDataSet()))->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))
            ->withPageSize(2)
            ->withPageToken(PageToken::previous('5'))
        ;

        $expected = self::getDataSet([1, 2]);
        $read = array_values($this->iterableToArray($paginator->read()));

        $this->assertSame($expected, $read);
        $first = reset($expected);
        $last = end($expected);
        $this->assertSame((string)$last['id'], $paginator->getNextPageTokenValue(), 'Last value fail!');
        $this->assertSame((string)$first['id'], $paginator->getPreviousPageTokenValue(), 'First value fail!');
    }

    public function testForwardAndBackwardPagination(): void
    {
        $sort = Sort::only(['id', 'name'])->withOrderString('id');
        $dataReader = (new IterableDataReader(self::getDataSet()))->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))
            ->withPageSize(2)
            ->withPageToken(PageToken::next('2'));

        $expected = self::getDataSet([2, 3]);
        $read = array_values($this->iterableToArray($paginator->read()));

        $this->assertSame($expected, $read);

        $first = reset($expected);
        $last = end($expected);

        $this->assertSame((string)$last['id'], $paginator->getNextPageTokenValue(), 'Last value fail!');
        $this->assertSame((string)$first['id'], $paginator->getPreviousPageTokenValue(), 'First value fail!');

        $paginator = (new KeysetPaginator($dataReader))
            ->withPageSize(2)
            ->withPageToken(PageToken::previous($paginator->getPreviousPageTokenValue()));

        $expected = self::getDataSet([0, 1]);
        $read = array_values($this->iterableToArray($paginator->read()));

        $this->assertSame($expected, $read);
        $last = end($expected);
        $this->assertSame((string)$last['id'], $paginator->getNextPageTokenValue(), 'Last value fail!');
        $this->assertNull($paginator->getPreviousPageTokenValue(), 'First value fail!');
    }

    public function testIsOnFirstPage(): void
    {
        $sort = Sort::only(['id'])->withOrderString('id');
        $dataReader = (new IterableDataReader(self::getDataSet()))->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))->withPageSize(2);

        $this->assertTrue($paginator->isOnFirstPage());
        $this->assertTrue($paginator->isPaginationRequired());
    }

    public function testIsOnLastPage(): void
    {
        $sort = Sort::only(['id'])->withOrderString('id');
        $dataReader = (new IterableDataReader(self::getDataSet()))->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))->withPageSize(2);

        $paginator = $paginator->withPageToken(PageToken::next('6'));
        $this->assertTrue($paginator->isOnLastPage());

        $paginator = $paginator->withPageToken(PageToken::next('5'));
        $this->assertTrue($paginator->isOnLastPage());

        $paginator = $paginator->withPageToken(PageToken::next('4'));
        $this->assertTrue($paginator->isOnLastPage());

        $paginator = $paginator->withPageToken(PageToken::next('3'));
        $this->assertTrue($paginator->isOnLastPage());

        $paginator = $paginator->withPageToken(PageToken::next('2'));
        $this->assertFalse($paginator->isOnLastPage());
        $this->assertTrue($paginator->isPaginationRequired());
    }

    public function testIsPaginationRequired(): void
    {
        $sort = Sort::only(['id'])->withOrderString('id');
        $dataReader = (new IterableDataReader(self::getDataSet()))->withSort($sort);
        $paginator = new KeysetPaginator($dataReader);

        $this->assertFalse($paginator->isPaginationRequired());

        $paginator = $paginator->withPageSize(2);

        $this->assertTrue($paginator->isPaginationRequired());
    }

    public function testCurrentPageSize(): void
    {
        $sort = Sort::only(['id'])->withOrderString('id');
        $dataReader = (new IterableDataReader(self::getDataSet()))->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))->withPageSize(2);
        $this->assertSame(2, $paginator->getCurrentPageSize());

        $paginator = $paginator->withPageToken(PageToken::previous('1'));
        $this->assertSame(0, $paginator->getCurrentPageSize());

        $paginator = $paginator->withPageToken(PageToken::previous('2'));
        $this->assertSame(1, $paginator->getCurrentPageSize());

        $paginator = $paginator->withPageToken(PageToken::previous('3'));
        $this->assertSame(2, $paginator->getCurrentPageSize());

        $paginator = $paginator->withPageToken(PageToken::next('6'));
        $this->assertSame(0, $paginator->getCurrentPageSize());

        $paginator = $paginator->withPageToken(PageToken::next('5'));
        $this->assertSame(1, $paginator->getCurrentPageSize());

        $paginator = $paginator->withPageToken(PageToken::next('4'));
        $this->assertSame(2, $paginator->getCurrentPageSize());
    }

    public function testReadCache(): void
    {
        $sort = Sort::only(['id'])->withOrderString('id');
        $dataSet = new class (self::getDataSet()) extends ArrayIterator {
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
        $dataReader = (new IterableDataReader(self::getDataSet()))->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))->withPageSize(2);
        $this->assertNotNull($paginator->getNextPageTokenValue());

        $paginator = $paginator->withPageToken(PageToken::previous('1'));
        try {
            $paginator->getNextPageTokenValue();
            $this->fail();
        } catch (RuntimeException) {
            $this->assertTrue(true);
        }
        $this->assertNull($paginator->getPreviousPageTokenValue());

        $paginator = $paginator->withPageToken(PageToken::previous('2'));
        $this->assertNotNull($paginator->getNextPageTokenValue());
        $this->assertSame('1', $paginator->getNextPageTokenValue());
        $this->assertNull($paginator->getPreviousPageTokenValue());

        $paginator = $paginator->withPageToken(PageToken::previous('3'));
        $this->assertNotNull($paginator->getNextPageTokenValue());
        $this->assertSame('2', $paginator->getNextPageTokenValue());
        $this->assertNull($paginator->getPreviousPageTokenValue());

        $paginator = $paginator->withPageToken(PageToken::next('6'));
        try {
            $paginator->getPreviousPageTokenValue();
            $this->fail();
        } catch (RuntimeException) {
            $this->assertTrue(true);
        }
        $this->assertNull($paginator->getNextPageTokenValue());

        $paginator = $paginator->withPageToken(PageToken::next('5'));
        $this->assertNotNull($paginator->getPreviousPageTokenValue());
        $this->assertSame('6', $paginator->getPreviousPageTokenValue());
        $this->assertNull($paginator->getNextPageTokenValue());

        $paginator = $paginator->withPageToken(PageToken::next('4'));
        $this->assertNull($paginator->getNextPageTokenValue());
        $this->assertNotNull($paginator->getPreviousPageTokenValue());
        $this->assertSame('5', $paginator->getPreviousPageTokenValue());
    }

    public function testDefaultPageSize(): void
    {
        $sort = Sort::only(['id'])->withOrderString('id');
        $dataReader = (new IterableDataReader(self::getDataSet()))->withSort($sort);
        $paginator = new KeysetPaginator($dataReader);

        $this->assertSame(10, $paginator->getPageSize());
        $this->assertCount(5, $paginator->read());
    }

    public function testCustomPageSize(): void
    {
        $sort = Sort::only(['id'])->withOrderString('id');
        $dataReader = (new IterableDataReader(self::getDataSet()))->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))->withPageSize(2);

        $this->assertSame(2, $paginator->getPageSize());
        $this->assertCount(2, $paginator->read());
    }

    private static function getDataSet(array $keys = null): array
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
        return new class () implements ReadableDataInterface, LimitableDataInterface, FilterableDataInterface {
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

            public function withFilterHandlers(FilterHandlerInterface ...$filterHandlers): static
            {
                return clone $this;
            }
        };
    }

    private function getNonFilterableDataReader()
    {
        return new class () implements ReadableDataInterface, LimitableDataInterface, SortableDataInterface {
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

    public function testGetSort(): void
    {
        $sort = Sort::only(['id'])->withOrderString('id');
        $dataReader = (new IterableDataReader(self::getDataSet()))->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader));

        $this->assertInstanceOf(Sort::class, $paginator->getSort());
    }

    public function testWithPreviousPageTokenAndIsOnFirstPageSameTrue(): void
    {
        $sort = Sort::only(['id'])->withOrderString('id');
        $dataReader = (new IterableDataReader(self::getDataSet()))->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))->withPageToken(PageToken::previous('1'));

        $this->assertTrue($paginator->isOnFirstPage());
    }

    public function testCloneClearValues(): void
    {
        $sort = Sort::only(['id'])->withOrderString('id');
        $dataReader = (new IterableDataReader(self::getDataSet()))->withSort($sort);
        $paginator = new KeysetPaginator($dataReader);
        $paginator->read();

        $this->assertSame(self::getDataSet(), $this->getInaccessibleProperty($paginator, 'readCache'));
        $this->assertSame('1', $this->getInaccessibleProperty($paginator, 'currentFirstValue'));
        $this->assertSame('6', $this->getInaccessibleProperty($paginator, 'currentLastValue'));

        $paginator = $paginator->withPageToken(PageToken::previous('1'));
        $paginator->read();
        $this->assertTrue($this->getInaccessibleProperty($paginator, 'hasNextPage'));

        $paginator = $paginator->withPageToken(PageToken::next('3'));
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
        $dataReader = (new IterableDataReader(self::getDataSet()))->withSort($sort);
        $paginator = new KeysetPaginator($dataReader);

        $this->assertNotSame($paginator, $paginator->withPageToken(PageToken::next('1')));
        $this->assertNotSame($paginator, $paginator->withPageToken(PageToken::previous('1')));
        $this->assertNotSame($paginator, $paginator->withPageSize(1));
        $this->assertNotSame($paginator, $paginator->withFilterCallback(null));
    }

    public function testGetPreviousPageExistForCoverage(): void
    {
        $sort = Sort::only(['id'])->withOrderString('id');
        $dataReader = (new IterableDataReader(self::getDataSet()))->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))->withPageToken(PageToken::next('1'));


        $this->assertTrue($this->invokeMethod($paginator, 'previousPageExist', [$dataReader, $sort]));
    }

    public function testGetFilterForCoverage(): void
    {
        $sort = Sort::only(['id'])->withOrderString('id');
        $dataReader = (new IterableDataReader([]))->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))->withPageToken(PageToken::next('1'));

        $this->assertInstanceOf(
            GreaterThan::class,
            $this->invokeMethod($paginator, 'getFilter', [$sort]),
        );

        $sort = Sort::only(['id'])
            ->withOrderString('id')
            ->withOrder(['id' => 'desc']);
        $dataReader = (new IterableDataReader([]))->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))->withPageToken(PageToken::next('1'));

        $this->assertInstanceOf(
            LessThan::class,
            $this->invokeMethod($paginator, 'getFilter', [$sort]),
        );
    }

    public function testGetReverseFilterForCoverage(): void
    {
        $sort = Sort::only(['42'])->withOrderString('42');
        $dataReader = (new IterableDataReader([]))->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))->withPageToken(PageToken::next('1'));

        $this->assertInstanceOf(
            LessThanOrEqual::class,
            $this->invokeMethod($paginator, 'getReverseFilter', [$sort]),
        );

        $sort = Sort::only(['42'])
            ->withOrderString('42')
            ->withOrder(['42' => 'desc']);
        $dataReader = (new IterableDataReader([]))->withSort($sort);
        $paginator = (new KeysetPaginator($dataReader))->withPageToken(PageToken::next('1'));

        $this->assertInstanceOf(
            GreaterThanOrEqual::class,
            $this->invokeMethod($paginator, 'getReverseFilter', [$sort]),
        );
    }

    public function testFilterCallback(): void
    {
        $dataReader = (new MutationDataReader(
            new IterableDataReader(self::DEFAULT_DATASET),
            static function ($item) {
                $item['id']--;
                return $item;
            }
        ))->withSort(Sort::only(['id'])->withOrderString('id'));
        $paginator = (new KeysetPaginator($dataReader))
            ->withPageSize(2)
            ->withPageToken(PageToken::previous('5'))
            ->withFilterCallback(
                static function (
                    GreaterThan|LessThan|GreaterThanOrEqual|LessThanOrEqual $filter,
                    KeysetFilterContext $context
                ): FilterInterface {
                    if ($context->field === 'id') {
                        $filter = $filter->withValue((string)($context->value + 1));
                    }
                    return $filter;
                }
            );

        $this->assertSame(
            [
                [
                    'id' => 2,
                    'name' => 'Agent K',
                ],
                [
                    'id' => 4,
                    'name' => 'Agent J',
                ],
            ],
            array_values($paginator->read())
        );
    }

    public function testFilterCallbackExtended(): void
    {
        $dataReader = (new MutationDataReader(
            new IterableDataReader(self::DEFAULT_DATASET),
            static function ($item) {
                $item['id']--;
                return $item;
            }
        ))->withSort(Sort::only(['id'])->withOrderString('id'));
        $paginator = (new KeysetPaginator($dataReader))
            ->withPageSize(2)
            ->withPageToken(PageToken::previous('2'))
            ->withFilterCallback(
                static function (
                    GreaterThan|LessThan|GreaterThanOrEqual|LessThanOrEqual $filter,
                    KeysetFilterContext $context
                ): FilterInterface {
                    $value = $context->field === 'id'
                        ? (string)($context->value + 1)
                        : $context->value;

                    if ($context->isReverse) {
                        $filter = $context->sorting === SORT_ASC
                            ? new LessThanOrEqual($context->field, $value)
                            : new GreaterThanOrEqual($context->field, $value);
                    } else {
                        $filter = $context->sorting === SORT_ASC
                            ? new GreaterThan($context->field, $value)
                            : new LessThan($context->field, $value);
                    }

                    return $filter;
                }
            );

        $this->assertSame(
            [
                [
                    'id' => 0,
                    'name' => 'Codename Boris',
                ],
                [
                    'id' => 1,
                    'name' => 'Codename Doris',
                ],
            ],
            array_values($paginator->read())
        );
    }

    public function testFilterCallbackWithReverse(): void
    {
        $dataReader = (new IterableDataReader(self::DEFAULT_DATASET))
            ->withSort(Sort::only(['id'])->withOrderString('id'));
        $paginator = (new KeysetPaginator($dataReader))
            ->withPageToken(PageToken::previous('1'))
            ->withFilterCallback(
                static function (
                    GreaterThan|LessThan|GreaterThanOrEqual|LessThanOrEqual $filter,
                    KeysetFilterContext $context
                ): FilterInterface {
                    if ($context->isReverse) {
                        return $context->sorting === SORT_ASC
                            ? new LessThanOrEqual($context->field, $context->value)
                            : new GreaterThanOrEqual($context->field, $context->value);
                    }
                    return $context->sorting === SORT_ASC
                        ? new GreaterThan($context->field, $context->value)
                        : new LessThan($context->field, $context->value);
                }
            );

        $this->assertTrue($paginator->isOnFirstPage());
        $this->assertFalse($paginator->isOnLastPage());
    }

    public static function dataPageTypeWithPreviousPageToken(): array
    {
        return [
            /**
             * Straight order
             * ['id' => 10]
             * ['id' => 11]
             * ['id' => 12]
             * ['id' => 13]
             */
            [true, false, [], '8'],
            [true, false, [], '9'],
            [true, false, [], '10'],
            [true, false, [10], '11'],
            [true, false, [10, 11], '12'],
            [false, false, [11, 12], '13'],
            [false, true, [12, 13], '14'],
            [false, true, [12, 13], '15'],

            /**
             * Reverse order
             * ['id' => 13]
             * ['id' => 12]
             * ['id' => 11]
             * ['id' => 10]
             */
            [false, true, [11, 10], '8', true],
            [false, true, [11, 10], '9', true],
            [false, false, [12, 11], '10', true],
            [true, false, [13, 12], '11', true],
            [true, false, [13], '12', true],
            [true, false, [], '13', true],
            [true, false, [], '14', true],
            [true, false, [], '15', true],
        ];
    }

    #[DataProvider('dataPageTypeWithPreviousPageToken')]
    public function testPageTypeWithPreviousPageToken(
        bool $expectedIsOnFirstPage,
        bool $expectedIsOnLastPage,
        array $expectedIds,
        string $token,
        bool $isReverseOrder = false
    ): void {
        $data = [
            ['id' => 10],
            ['id' => 11],
            ['id' => 12],
            ['id' => 13],
        ];
        $sort = Sort::only(['id'])->withOrderString($isReverseOrder ? '-id' : 'id');
        $reader = (new IterableDataReader($data))->withSort($sort);

        $paginator = (new KeysetPaginator($reader))
            ->withPageSize(2)
            ->withPageToken(PageToken::previous($token));

        $this->assertSame($expectedIsOnFirstPage, $paginator->isOnFirstPage());
        $this->assertSame($expectedIsOnLastPage, $paginator->isOnLastPage());
        $this->assertSame($expectedIds, ArrayHelper::getColumn($paginator->read(), 'id', keepKeys: false));
    }

    public static function dataPageTypeWithNextPageToken(): array
    {
        return [
            /**
             * Straight order
             * ['id' => 10]
             * ['id' => 11]
             * ['id' => 12]
             * ['id' => 13]
             */
            [true, false, [10, 11], '8'],
            [true, false, [10, 11], '9'],
            [false, false, [11, 12], '10'],
            [false, true, [12, 13], '11'],
            [false, true, [13], '12'],
            [false, true, [], '13'],
            [false, true, [], '14'],
            [false, true, [], '15'],

            /**
             * Reverse order
             * ['id' => 13]
             * ['id' => 12]
             * ['id' => 11]
             * ['id' => 10]
             */
            [false, true, [], '8', true],
            [false, true, [], '9', true],
            [false, true, [], '10', true],
            [false, true, [10], '11', true],
            [false, true, [11, 10], '12', true],
            [false, false, [12, 11], '13', true],
            [true, false, [13, 12], '14', true],
            [true, false, [13, 12], '15', true],
        ];
    }

    #[DataProvider('dataPageTypeWithNextPageToken')]
    public function testPageTypeWithNextPageToken(
        bool $expectedIsOnFirstPage,
        bool $expectedIsOnLastPage,
        array $expectedIds,
        string $token,
        bool $isReverseOrder = false
    ): void {
        $data = [
            ['id' => 10],
            ['id' => 11],
            ['id' => 12],
            ['id' => 13],
        ];
        $sort = Sort::only(['id'])->withOrderString($isReverseOrder ? '-id' : 'id');
        $reader = (new IterableDataReader($data))->withSort($sort);

        $paginator = (new KeysetPaginator($reader))
            ->withPageSize(2)
            ->withPageToken(PageToken::next($token));

        $this->assertSame($expectedIsOnFirstPage, $paginator->isOnFirstPage());
        $this->assertSame($expectedIsOnLastPage, $paginator->isOnLastPage());
        $this->assertSame($expectedIds, ArrayHelper::getColumn($paginator->read(), 'id', keepKeys: false));
    }

    public function testIsSortable(): void
    {
        $sort = Sort::only(['id'])->withOrderString('id');
        $reader = (new IterableDataReader([]))->withSort($sort);
        $paginator = new KeysetPaginator($reader);

        $this->assertTrue($paginator->isSortable());
    }

    public function testWithSort(): void
    {
        $sort1 = Sort::only(['id'])->withOrderString('id');
        $reader = (new IterableDataReader([]))->withSort($sort1);
        $paginator1 = new KeysetPaginator($reader);

        $sort2 = $sort1->withOrderString('-id');
        $paginator2 = $paginator1->withSort($sort2);

        $this->assertNotSame($paginator1, $paginator2);
        $this->assertSame($sort1, $paginator1->getSort());
        $this->assertSame($sort2, $paginator2->getSort());
    }

    public function testGetPageToken(): void
    {
        $sort = Sort::only(['id'])->withOrderString('id');
        $reader = (new IterableDataReader([]))->withSort($sort);
        $paginator = new KeysetPaginator($reader);

        $this->assertNull($paginator->getPageToken());

        $token = PageToken::next('1');
        $paginator = $paginator->withPageToken($token);

        $this->assertSame($token, $paginator->getPageToken());
    }
}
