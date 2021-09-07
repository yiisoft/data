<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader;

use PHPUnit\Framework\TestCase;
use Yiisoft\Data\Reader\Filter\All;
use Yiisoft\Data\Reader\Filter\Any;
use Yiisoft\Data\Reader\Filter\Equals;
use Yiisoft\Data\Reader\Filter\FilterInterface;
use Yiisoft\Data\Reader\Filter\GreaterThan;
use Yiisoft\Data\Reader\Filter\GreaterThanOrEqual;
use Yiisoft\Data\Reader\Filter\In;
use Yiisoft\Data\Reader\Filter\LessThan;
use Yiisoft\Data\Reader\Filter\LessThanOrEqual;
use Yiisoft\Data\Reader\Filter\Like;
use Yiisoft\Data\Reader\Filter\Not;
use Yiisoft\Data\Reader\Iterable\IterableDataReader;
use Yiisoft\Data\Reader\Sort;

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

    private function getDataSetSortedByName(): array
    {
        return [
            4 => self::ITEM_5,
            3 => self::ITEM_4,
            2 => self::ITEM_3,
            0 => self::ITEM_1,
            1 => self::ITEM_2,
        ];
    }

    public function testWithLimitIsImmutable(): void
    {
        $reader = new IterableDataReader([]);

        $this->assertNotSame($reader, $reader->withLimit(1));
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

    public function testsWithSortIsImmutable(): void
    {
        $reader = new IterableDataReader([]);

        $this->assertNotSame($reader, $reader->withSort(null));
    }

    public function testSorting(): void
    {
        $sorting = Sort::only([
            'id',
            'name',
        ]);

        $sorting = $sorting->withOrder(['name' => 'asc']);

        $reader = (new IterableDataReader(self::DEFAULT_DATASET))
            ->withSort($sorting);

        $data = $reader->read();

        $this->assertSame($this->getDataSetSortedByName(), $data);
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

        $this->assertSame($this->getDataSetSortedByName()[2], $reader->readOne());
    }

    public function testsWithFilterIsImmutable(): void
    {
        $reader = new IterableDataReader([]);

        $this->assertNotSame($reader, $reader->withFilter(null));
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
        $reader = new IterableDataReader(new \ArrayIterator(self::DEFAULT_DATASET));
        $sorting = Sort::only([
            'id',
            'name',
        ]);
        $sorting = $sorting->withOrder(['name' => 'asc']);
        $this->assertSame($this->getDataSetSortedByName(), $reader->withSort($sorting)->read());
    }

    private function getDataSetAsGenerator(): \Generator
    {
        yield from self::DEFAULT_DATASET;
    }

    public function testGeneratorAsDataSet(): void
    {
        $reader = new IterableDataReader($this->getDataSetAsGenerator());
        $sorting = Sort::only([
            'id',
            'name',
        ]);
        $sorting = $sorting->withOrder(['name' => 'asc']);
        $this->assertSame($this->getDataSetSortedByName(), $reader->withSort($sorting)->read());
    }

    public function testCustomFilter(): void
    {
        $digitalFilter = new class /*Digital*/ ('name') implements FilterInterface {
            private $field;

            public function __construct(string $field)
            {
                $this->field = $field;
            }

            public function toArray(): array
            {
                return [self::getOperator(), $this->field];
            }

            public static function getOperator(): string
            {
                return 'digital';
            }
        };
        $reader = new class (self::DEFAULT_DATASET) extends IterableDataReader {
            protected function matchFilter(array $item, array $filter): bool
            {
                [$operation, $field] = $filter;

                if ($operation === 'digital' /*Digital::getOperator()*/) {
                    return ctype_digit($item[$field]);
                }

                return parent::matchFilter($item, $filter);
            }
        };

        $reader = $reader->withFilter($digitalFilter);

        $filtered = $reader->read();
        $this->assertSame([4 => self::ITEM_5], $filtered);
    }

    public function testNotSupportedOperator(): void
    {
        $dataReader = (new IterableDataReader(self::DEFAULT_DATASET))
            ->withFilter(new class ('id', 2) extends Equals {
                public static function getOperator(): string
                {
                    return '----';
                }
            });
        $this->expectException(\RuntimeException::class);
        $dataReader->read();
    }
}
