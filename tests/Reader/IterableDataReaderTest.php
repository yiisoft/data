<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader;

use ArrayIterator;
use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Yiisoft\Data\Reader\DataReaderException;
use Yiisoft\Data\Reader\Filter\All;
use Yiisoft\Data\Reader\Filter\Any;
use Yiisoft\Data\Reader\Filter\Equals;
use Yiisoft\Data\Reader\Filter\GreaterThan;
use Yiisoft\Data\Reader\Filter\GreaterThanOrEqual;
use Yiisoft\Data\Reader\Filter\In;
use Yiisoft\Data\Reader\Filter\LessThan;
use Yiisoft\Data\Reader\Filter\LessThanOrEqual;
use Yiisoft\Data\Reader\Filter\Like;
use Yiisoft\Data\Reader\Filter\Not;
use Yiisoft\Data\Reader\FilterHandlerInterface;
use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\Iterable\IterableDataReader;
use Yiisoft\Data\Reader\Iterable\IterableFilterHandlerInterface;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Data\Tests\Support\CustomFilter\Digital;
use Yiisoft\Data\Tests\Support\CustomFilter\DigitalHandler;
use Yiisoft\Data\Tests\Support\CustomFilter\FilterWithoutHandler;
use Yiisoft\Data\Tests\TestCase;

use function array_slice;
use function array_values;
use function count;

final class IterableDataReaderTest extends TestCase
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

    public function testImmutability(): void
    {
        $reader = new IterableDataReader([]);

        $this->assertNotSame($reader, $reader->withFilterHandlers());
        $this->assertNotSame($reader, $reader->withFilter(null));
        $this->assertNotSame($reader, $reader->withSort(null));
        $this->assertNotSame($reader, $reader->withOffset(1));
        $this->assertNotSame($reader, $reader->withLimit(1));
    }

    public function testExceptionOnPassingNonIterableFilters(): void
    {
        $nonIterableFilterHandler = new class () implements FilterHandlerInterface {
            public function getFilterClass(): string
            {
                return '?';
            }
        };

        $this->expectException(DataReaderException::class);
        $message = sprintf(
            '%s::withFilterHandlers() accepts instances of %s only.',
            IterableDataReader::class,
            IterableFilterHandlerInterface::class
        );
        $this->expectExceptionMessage($message);

        (new IterableDataReader([]))->withFilterHandlers($nonIterableFilterHandler);
    }

    public function testWithLimitFailForNegativeValues(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The limit must not be less than 0.');

        (new IterableDataReader([]))->withLimit(-1);
    }

    public function testLimitIsApplied(): void
    {
        $reader = (new IterableDataReader(self::DEFAULT_DATASET))
            ->withLimit(5);

        $data = $reader->read();

        $this->assertCount(5, $data);
        $this->assertSame(array_slice(self::DEFAULT_DATASET, 0, 5), $data);
    }

    public function testOffsetIsApplied(): void
    {
        $reader = (new IterableDataReader(self::DEFAULT_DATASET))
            ->withOffset(2);

        $data = $reader->read();

        $this->assertCount(3, $data);
        $this->assertSame([
            2 => self::ITEM_3,
            3 => self::ITEM_4,
            4 => self::ITEM_5,
        ], $data);
    }

    public function testAscSorting(): void
    {
        $sorting = Sort::only([
            'id',
            'name',
        ]);

        $sorting = $sorting->withOrder(['name' => 'asc']);

        $reader = (new IterableDataReader(self::DEFAULT_DATASET))
            ->withSort($sorting);

        $data = $reader->read();

        $this->assertSame($this->getDataSetAscSortedByName(), $data);
    }

    public function testDescSorting(): void
    {
        $sorting = Sort::only([
            'id',
            'name',
        ]);

        $sorting = $sorting->withOrder(['name' => 'desc']);

        $reader = (new IterableDataReader(self::DEFAULT_DATASET))
            ->withSort($sorting);

        $data = $reader->read();

        $this->assertSame($this->getDataSetDescSortedByName(), $data);
    }

    public function testCounting(): void
    {
        $reader = new IterableDataReader(self::DEFAULT_DATASET);
        $this->assertSame(5, $reader->count());
        $this->assertCount(5, $reader);
    }

    public function testReadOne(): void
    {
        $data = self::DEFAULT_DATASET;
        $reader = new IterableDataReader($data);

        $this->assertSame($data[0], $reader->readOne());
    }

    public function testReadOneWithSortingAndOffset(): void
    {
        $sorting = Sort::only(['id', 'name'])->withOrder(['name' => 'asc']);

        $data = self::DEFAULT_DATASET;
        $reader = (new IterableDataReader($data))
            ->withSort($sorting)
            ->withOffset(2);

        $this->assertSame($this->getDataSetAscSortedByName()[2], $reader->readOne());
    }

    public function testEqualsFiltering(): void
    {
        $filter = new Equals('id', 3);
        $reader = (new IterableDataReader(self::DEFAULT_DATASET))
            ->withFilter($filter);

        $this->assertSame([
            2 => self::ITEM_3,
        ], $reader->read());
    }

    public function testGreaterThanFiltering(): void
    {
        $filter = new GreaterThan('id', 3);
        $reader = (new IterableDataReader(self::DEFAULT_DATASET))
            ->withFilter($filter);

        $this->assertSame([
            3 => self::ITEM_4,
            4 => self::ITEM_5,
        ], $reader->read());
    }

    public function testGreaterThanOrEqualFiltering(): void
    {
        $filter = new GreaterThanOrEqual('id', 3);
        $reader = (new IterableDataReader(self::DEFAULT_DATASET))
            ->withFilter($filter);

        $this->assertSame([
            2 => self::ITEM_3,
            3 => self::ITEM_4,
            4 => self::ITEM_5,
        ], $reader->read());
    }

    public function testLessThanFiltering(): void
    {
        $filter = new LessThan('id', 3);
        $reader = (new IterableDataReader(self::DEFAULT_DATASET))
            ->withFilter($filter);

        $this->assertSame([
            0 => self::ITEM_1,
            1 => self::ITEM_2,
        ], $reader->read());
    }

    public function testLessThanOrEqualFiltering(): void
    {
        $filter = new LessThanOrEqual('id', 3);
        $reader = (new IterableDataReader(self::DEFAULT_DATASET))
            ->withFilter($filter);

        $this->assertSame([
            0 => self::ITEM_1,
            1 => self::ITEM_2,
            2 => self::ITEM_3,
        ], $reader->read());
    }

    public function testInFiltering(): void
    {
        $filter = new In('id', [1, 2]);
        $reader = (new IterableDataReader(self::DEFAULT_DATASET))
            ->withFilter($filter);

        $this->assertSame([
            0 => self::ITEM_1,
            1 => self::ITEM_2,
        ], $reader->read());
    }

    public function testLikeFiltering(): void
    {
        $filter = new Like('name', 'agent');
        $reader = (new IterableDataReader(self::DEFAULT_DATASET))
            ->withFilter($filter);

        $this->assertSame([
            2 => self::ITEM_3,
            3 => self::ITEM_4,
        ], $reader->read());
    }

    public function testNotFiltering(): void
    {
        $filter = new Not(new Equals('id', 1));
        $reader = (new IterableDataReader(self::DEFAULT_DATASET))
            ->withFilter($filter);

        $this->assertSame([
            1 => self::ITEM_2,
            2 => self::ITEM_3,
            3 => self::ITEM_4,
            4 => self::ITEM_5,
        ], $reader->read());
    }

    public function testAnyFiltering(): void
    {
        $filter = new Any(
            new Equals('id', 1),
            new Equals('id', 2)
        );
        $reader = (new IterableDataReader(self::DEFAULT_DATASET))
            ->withFilter($filter);

        $this->assertSame([
            0 => self::ITEM_1,
            1 => self::ITEM_2,
        ], $reader->read());
    }

    public function testAllFiltering(): void
    {
        $filter = new All(
            new GreaterThan('id', 3),
            new Like('name', 'agent')
        );
        $reader = (new IterableDataReader(self::DEFAULT_DATASET))
            ->withFilter($filter);

        $this->assertSame([
            3 => self::ITEM_4,
        ], $reader->read());
    }

    public function testLimitedSort(): void
    {
        $readerMin = (new IterableDataReader(self::DEFAULT_DATASET))
            ->withSort(
                Sort::only(['id'])->withOrder(['id' => 'asc'])
            )
            ->withLimit(1);
        $min = $readerMin->read()[0]['id'];
        $this->assertSame(1, $min, 'Wrong min value found');

        $readerMax = (new IterableDataReader(self::DEFAULT_DATASET))
            ->withSort(
                Sort::only(['id'])->withOrder(['id' => 'desc'])
            )
            ->withLimit(1);
        $max = $readerMax->readOne()['id'];
        $this->assertSame(6, $max, 'Wrong max value found');
    }

    public function testFilteredCount(): void
    {
        $reader = new IterableDataReader(self::DEFAULT_DATASET);
        $total = count($reader);

        $this->assertSame(5, $total, 'Wrong count of elements');

        $reader = $reader->withFilter(new Like('name', 'agent'));
        $totalAgents = count($reader);
        $this->assertSame(2, $totalAgents, 'Wrong count of filtered elements');
    }

    public function testIteratorIteratorAsDataSet(): void
    {
        $reader = new IterableDataReader(new ArrayIterator(self::DEFAULT_DATASET));
        $sorting = Sort::only([
            'id',
            'name',
        ]);
        $sorting = $sorting->withOrder(['name' => 'asc']);
        $this->assertSame(
            $this->getDataSetAscSortedByName(),
            $reader
                ->withSort($sorting)
                ->read(),
        );
    }

    public function testGeneratorAsDataSet(): void
    {
        $reader = new IterableDataReader($this->getDataSetAsGenerator());
        $sorting = Sort::only([
            'id',
            'name',
        ]);
        $sorting = $sorting->withOrder(['name' => 'asc']);
        $this->assertSame(
            $this->getDataSetAscSortedByName(),
            $reader
                ->withSort($sorting)
                ->read(),
        );
    }

    public function testCustomFilter(): void
    {
        $reader = (new IterableDataReader(self::DEFAULT_DATASET))
            ->withFilterHandlers(new DigitalHandler())
            ->withFilter(
                new All(new GreaterThan('id', 0), new Digital('name'))
            );

        $filtered = $reader->read();
        $this->assertSame([4 => self::ITEM_5], $filtered);
    }

    public function testCustomEqualsProcessor(): void
    {
        $sort = Sort::only(['id', 'name'])->withOrderString('id');

        $dataReader = (new IterableDataReader(self::DEFAULT_DATASET))
            ->withSort($sort)
            ->withFilterHandlers(
                new class () implements IterableFilterHandlerInterface {
                    public function getFilterClass(): string
                    {
                        return Equals::class;
                    }

                    public function match(
                        array|object $item,
                        FilterInterface $filter,
                        array $iterableFilterHandlers
                    ): bool {
                        /** @var Equals $filter */
                        return $item[$filter->field] === 2;
                    }
                }
            );

        $dataReader = $dataReader->withFilter(new Equals('id', 100));
        $expected = [self::ITEM_2];

        $this->assertSame($expected, array_values($this->iterableToArray($dataReader->read())));
    }

    public function testNotSupportedFilter(): void
    {
        $dataReader = (new IterableDataReader(self::DEFAULT_DATASET))
            ->withFilter(new FilterWithoutHandler());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Filter "' . FilterWithoutHandler::class . '" is not supported.');

        $dataReader->read();
    }

    public function testArrayOfObjects(): void
    {
        $data = [
            'one' => new class () {
                public int $a = 1;
            },
            'two' => new class () {
                public int $a = 2;
            },
            'three' => new class () {
                public int $a = 3;
            },
        ];

        $reader = new IterableDataReader($data);

        $rows = $reader->withFilter(new In('a', [2, 3]))->read();

        $this->assertSame(['two', 'three'], array_keys($rows));
        $this->assertSame(2, $rows['two']->a);
        $this->assertSame(3, $rows['three']->a);
    }

    public function testSortingWithSameValues(): void
    {
        $data = [
            0 => ['value' => 1],
            1 => ['value' => 2],
            2 => ['value' => 3],
            3 => ['value' => 2],
        ];

        $reader = (new IterableDataReader($data))
            ->withSort(
                Sort::any()->withOrder(['value' => 'asc'])
            );

        $this->assertSame(
            [
                0 => ['value' => 1],
                1 => ['value' => 2],
                3 => ['value' => 2],
                2 => ['value' => 3],
            ],
            $reader->read()
        );
    }

    public function testWithLimitZero(): void
    {
        $data = [
            0 => ['value' => 1],
            1 => ['value' => 2],
            2 => ['value' => 3],
            3 => ['value' => 2],
        ];

        $reader = (new IterableDataReader($data))
            ->withLimit(2)
            ->withLimit(0);

        $this->assertSame(
            [
                0 => ['value' => 1],
                1 => ['value' => 2],
                2 => ['value' => 3],
                3 => ['value' => 2],
            ],
            $reader->read()
        );
    }

    private function getDataSetAsGenerator(): Generator
    {
        yield from self::DEFAULT_DATASET;
    }

    private function getDataSetAscSortedByName(): array
    {
        return [
            4 => self::ITEM_5,
            3 => self::ITEM_4,
            2 => self::ITEM_3,
            0 => self::ITEM_1,
            1 => self::ITEM_2,
        ];
    }

    private function getDataSetDescSortedByName(): array
    {
        return [
            1 => self::ITEM_2,
            0 => self::ITEM_1,
            2 => self::ITEM_3,
            3 => self::ITEM_4,
            4 => self::ITEM_5,
        ];
    }
}
