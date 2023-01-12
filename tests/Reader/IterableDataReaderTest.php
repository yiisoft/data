<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader;

use ArrayIterator;
use DateTimeInterface;
use Generator;
use InvalidArgumentException;
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
use Yiisoft\Data\Reader\FilterAssert;
use Yiisoft\Data\Reader\FilterHandlerInterface;
use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\Iterable\FilterHandler\CompareHandler;
use Yiisoft\Data\Reader\Iterable\IterableDataReader;
use Yiisoft\Data\Reader\Iterable\IterableFilterHandlerInterface;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Data\Tests\Support\CustomFilter\DigitalHandler;
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
            public function getOperator(): string
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
        $digitalFilter = new class /*Digital*/ ('name') implements FilterInterface {
            public function __construct(private string $field)
            {
            }

            public function toCriteriaArray(): array
            {
                return [self::getOperator(), $this->field];
            }

            public static function getOperator(): string
            {
                return 'digital';
            }
        };

        $reader = (new IterableDataReader(self::DEFAULT_DATASET))
            ->withFilterHandlers(new DigitalHandler())
            ->withFilter($digitalFilter);

        $filtered = $reader->read();
        $this->assertSame([4 => self::ITEM_5], $filtered);
    }

    public function testCustomEqualsProcessor(): void
    {
        $sort = Sort::only(['id', 'name'])->withOrderString('id');

        $dataReader = (new IterableDataReader(self::DEFAULT_DATASET))
            ->withSort($sort)
            ->withFilterHandlers(new class () extends CompareHandler {
                public function getOperator(): string
                {
                    return Equals::getOperator();
                }

                protected function compare(mixed $itemValue, mixed $argumentValue): bool
                {
                    if (!$itemValue instanceof DateTimeInterface) {
                        return $itemValue == $argumentValue;
                    }

                    return $argumentValue instanceof DateTimeInterface
                        && $itemValue->getTimestamp() === $argumentValue->getTimestamp();
                }

                public function match(array|object $item, array $arguments, array $iterableFilterHandlers): bool
                {
                    if (count($arguments) !== 2) {
                        throw new InvalidArgumentException('$arguments should contain exactly two elements.');
                    }

                    [$field, $value] = $arguments;
                    FilterAssert::fieldIsString($field);

                    if ($item[$field] === 2) {
                        return true;
                    }

                    /** @var string $field */
                    return array_key_exists($field, $item) && $this->compare($item[$field], $value);
                }
            });

        $dataReader = $dataReader->withFilter(new Equals('id', 100));
        $expected = [self::ITEM_2];

        $this->assertSame($expected, array_values($this->iterableToArray($dataReader->read())));
    }

    /**
     * @dataProvider invalidStringValueDataProvider
     */
    public function testMatchFilterFailIfOperatorIsNotString($operator): void
    {
        $reader = (new IterableDataReader(self::DEFAULT_DATASET))
            ->withFilter(
                new class ($operator) implements FilterInterface {
                    public function __construct(
                        private mixed $operator
                    ) {
                    }

                    public static function getOperator(): string
                    {
                        return 'custom-filter';
                    }

                    public function toCriteriaArray(): array
                    {
                        return [$this->operator, 'field', 'value'];
                    }
                }
            );

        $type = get_debug_type($operator);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("The operator should be string. The $type is received.");
        $reader->read();
    }

    public function testNotSupportedOperator(): void
    {
        $dataReader = (new IterableDataReader(self::DEFAULT_DATASET))
            ->withFilter($this->createFilterWithNotSupportedOperator('---'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Operation "---" is not supported.');

        $dataReader->read();
    }

    public function testNotSupportedEmptyOperator(): void
    {
        $dataReader = (new IterableDataReader(self::DEFAULT_DATASET))
            ->withFilter($this->createFilterWithNotSupportedOperator(''));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The operator string cannot be empty.');

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

    private function createFilterWithNotSupportedOperator(string $operator): FilterInterface
    {
        return new class ($operator) implements FilterInterface {
            private static string $operator;

            public function __construct(string $operator)
            {
                self::$operator = $operator;
            }

            public static function getOperator(): string
            {
                return self::$operator;
            }

            public function toCriteriaArray(): array
            {
                return [self::getOperator(), self::$operator];
            }
        };
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
