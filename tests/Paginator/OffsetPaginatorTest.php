<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Paginator;

use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use Yiisoft\Data\Paginator\OffsetPaginator;
use Yiisoft\Data\Paginator\PageNotFoundException;
use Yiisoft\Data\Paginator\PageToken;
use Yiisoft\Data\Paginator\PaginatorInterface;
use Yiisoft\Data\Reader\CountableDataInterface;
use Yiisoft\Data\Reader\Filter\Equals;
use Yiisoft\Data\Reader\Iterable\IterableDataReader;
use Yiisoft\Data\Reader\LimitableDataInterface;
use Yiisoft\Data\Reader\OffsetableDataInterface;
use Yiisoft\Data\Reader\ReadableDataInterface;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Data\Tests\Support\StubOffsetData;
use Yiisoft\Data\Tests\TestCase;

final class OffsetPaginatorTest extends TestCase
{
    use PageTokenAssertTrait;

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

    public function testDataReaderWithoutOffsetableInterface(): void
    {
        $nonOffsetableDataReader = new class () implements ReadableDataInterface, CountableDataInterface {
            public function withLimit(int $limit): static
            {
                // do nothing
                return $this;
            }

            public function read(): iterable
            {
                return [];
            }

            public function readOne(): array|object|null
            {
                return null;
            }

            public function count(): int
            {
                return 0;
            }
        };

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Data reader should implement "%s" in order to be used with offset paginator.',
            OffsetableDataInterface::class
        ));

        new OffsetPaginator($nonOffsetableDataReader);
    }

    public function testDataReaderWithoutCountableInterface(): void
    {
        $nonCountableDataReader = new class () implements ReadableDataInterface, OffsetableDataInterface {
            public function withLimit(int $limit): static
            {
                // do nothing
                return $this;
            }

            public function read(): iterable
            {
                return [];
            }

            public function readOne(): array|object|null
            {
                return null;
            }

            public function count(): int
            {
                return 0;
            }

            public function withOffset(int $offset): static
            {
                // do nothing
                return $this;
            }

            public function getOffset(): int
            {
                return 0;
            }
        };

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Data reader should implement "%s" in order to be used with offset paginator.',
            CountableDataInterface::class,
        ));

        new OffsetPaginator($nonCountableDataReader);
    }

    public function testDataReaderWithoutLimitableInterface(): void
    {
        $nonLimitableDataReader = new class () implements
            ReadableDataInterface,
            CountableDataInterface,
            OffsetableDataInterface {
            public function read(): iterable
            {
                return [];
            }

            public function readOne(): array|object|null
            {
                return null;
            }

            public function count(): int
            {
                return 0;
            }

            public function withOffset(int $offset): static
            {
                // do nothing
                return $this;
            }

            public function getOffset(): int
            {
                return 0;
            }
        };

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Data reader should implement "%s" in order to be used with offset paginator.',
                LimitableDataInterface::class,
            )
        );

        new OffsetPaginator($nonLimitableDataReader);
    }

    public function testDefaultState(): void
    {
        $dataReader = new IterableDataReader(self::DEFAULT_DATASET);
        $paginator = new OffsetPaginator($dataReader);

        $this->assertSame(0, $paginator->getOffset());
        $this->assertSame(1, $paginator->getCurrentPage());
        $this->assertTrue($paginator->isOnFirstPage());
        $this->assertFalse($paginator->isPaginationRequired());
    }

    public function testIsPaginationRequired(): void
    {
        $dataReader = new IterableDataReader(self::DEFAULT_DATASET);
        $paginator = (new OffsetPaginator($dataReader))->withPageSize(2);

        $this->assertTrue($paginator->isPaginationRequired());
    }

    public function testGetTotalItems(): void
    {
        $dataReader = new IterableDataReader(self::DEFAULT_DATASET);
        $paginator = new OffsetPaginator($dataReader);

        $this->assertSame(5, $paginator->getTotalItems());
    }

    public function testWithCurrentPage(): void
    {
        $dataReader = new IterableDataReader(self::DEFAULT_DATASET);
        $paginator = new OffsetPaginator($dataReader);
        $newPaginator = $paginator->withCurrentPage(20);

        $this->assertSame(1, $paginator->getCurrentPage());
        $this->assertSame(20, $newPaginator->getCurrentPage());
    }

    public function testCurrentPageCannotBeLessThanOne(): void
    {
        $dataReader = new IterableDataReader(self::DEFAULT_DATASET);
        $paginator = new OffsetPaginator($dataReader);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Current page should be at least 1.');

        $paginator->withCurrentPage(0);
    }

    public function testReadCurrentPageCannotBeLargerThanMaxPages(): void
    {
        $dataReader = new IterableDataReader(self::DEFAULT_DATASET);
        $paginator = (new OffsetPaginator($dataReader))
            ->withPageSize(2)
            ->withCurrentPage(4)
        ;

        $this->assertSame(3, $paginator->getTotalPages());
        $this->expectException(PageNotFoundException::class);
        $this->expectExceptionMessage('Page 4 not found.');

        $this->iterableToArray($paginator->read());
    }

    public function testWithPageSize(): void
    {
        $dataReader = new IterableDataReader(self::DEFAULT_DATASET);
        $paginator = new OffsetPaginator($dataReader);
        $newPaginator = $paginator->withPageSize(125);

        $this->assertSame(PaginatorInterface::DEFAULT_PAGE_SIZE, $paginator->getPageSize());
        $this->assertSame(125, $newPaginator->getPageSize());
    }

    public function testPageSizeCannotBeLessThanOne(): void
    {
        $dataReader = new IterableDataReader(self::DEFAULT_DATASET);
        $paginator = new OffsetPaginator($dataReader);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Page size should be at least 1.');

        $paginator->withPageSize(0);
    }

    public function testReadFirstPage(): void
    {
        $dataReader = new IterableDataReader(self::DEFAULT_DATASET);

        $paginator = (new OffsetPaginator($dataReader))
            ->withPageSize(2)
            ->withCurrentPage(1)
        ;

        $expected = [
            self::ITEM_1,
            self::ITEM_2,
        ];

        $this->assertSame($expected, $this->iterableToArray($paginator->read()));
    }

    public function testReadSecondPage(): void
    {
        $dataReader = new IterableDataReader(self::DEFAULT_DATASET);

        $paginator = (new OffsetPaginator($dataReader))
            ->withPageSize(2)
            ->withCurrentPage(2)
        ;

        $expected = [
            self::ITEM_3,
            self::ITEM_4,
        ];

        $this->assertSame($expected, array_values($this->iterableToArray($paginator->read())));
    }

    public function testReadLastPage(): void
    {
        $dataReader = new IterableDataReader(self::DEFAULT_DATASET);

        $paginator = (new OffsetPaginator($dataReader))
            ->withPageSize(2)
            ->withCurrentPage(3)
        ;

        $expected = [
            self::ITEM_5,
        ];

        $this->assertSame($expected, array_values($this->iterableToArray($paginator->read())));
    }

    public static function dataReadOne(): array
    {
        $data = [];

        $data['empty'] = [
            null,
            new OffsetPaginator(new IterableDataReader([])),
        ];

        $data['base'] = [
            ['id' => 2, 'name' => 'John'],
            (new OffsetPaginator(
                new IterableDataReader([
                    ['id' => 1, 'name' => 'Mike'],
                    ['id' => 2, 'name' => 'John'],
                ])
            ))->withPageSize(1)->withCurrentPage(2),
        ];

        return $data;
    }

    #[DataProvider('dataReadOne')]
    public function testReadOne(mixed $expected, OffsetPaginator $paginator): void
    {
        $result = $paginator->readOne();
        $this->assertSame($expected, $result);
    }

    public function testTotalPages(): void
    {
        $dataReader = new IterableDataReader(self::DEFAULT_DATASET);
        $paginator = (new OffsetPaginator($dataReader))->withPageSize(2);

        $this->assertSame(3, $paginator->getTotalPages());
    }

    public function testRoundingUpTotalPages(): void
    {
        $dataReader = new IterableDataReader(self::DEFAULT_DATASET);
        $paginator = (new OffsetPaginator($dataReader))->withPageSize(4);

        $this->assertSame(2, $paginator->getTotalPages());
    }

    public function testIsFirstPageOnFirstPage(): void
    {
        $dataReader = new IterableDataReader(self::DEFAULT_DATASET);
        $paginator = (new OffsetPaginator($dataReader))
            ->withPageSize(2)
            ->withCurrentPage(1)
        ;

        $this->assertTrue($paginator->isOnFirstPage());
    }

    public function testIsFirstPageOnSecondPage(): void
    {
        $dataReader = new IterableDataReader(self::DEFAULT_DATASET);
        $paginator = (new OffsetPaginator($dataReader))
            ->withPageSize(2)
            ->withCurrentPage(2)
        ;

        $this->assertFalse($paginator->isOnFirstPage());
    }

    public function testIsLastPageOnFirstPage(): void
    {
        $dataReader = new IterableDataReader(self::DEFAULT_DATASET);
        $paginator = (new OffsetPaginator($dataReader))
            ->withPageSize(2)
            ->withCurrentPage(1)
        ;

        $this->assertFalse($paginator->isOnLastPage());
    }

    public function testIsLastPageOnLastPage(): void
    {
        $dataReader = new IterableDataReader(self::DEFAULT_DATASET);
        $paginator = (new OffsetPaginator($dataReader))
            ->withPageSize(2)
            ->withCurrentPage(3)
        ;

        $this->assertTrue($paginator->isOnLastPage());
    }

    public function testGetCurrentPageSizeFirstFullPage(): void
    {
        $dataReader = new IterableDataReader(self::DEFAULT_DATASET);
        $paginator = (new OffsetPaginator($dataReader))->withPageSize(3);

        $this->assertSame(3, $paginator->getCurrentPageSize());
    }

    public function testGetCurrentPageSizeLastPage(): void
    {
        $dataReader = new IterableDataReader(self::DEFAULT_DATASET);
        $paginator = (new OffsetPaginator($dataReader))
            ->withPageSize(3)
            ->withCurrentPage(2)
        ;

        $this->assertSame(2, $paginator->getCurrentPageSize());
        $this->assertSame(5, $paginator->getTotalItems());
    }

    public function testGetCurrentPageSizeFirstNotFullPage(): void
    {
        $dataReader = new IterableDataReader(self::DEFAULT_DATASET);
        $paginator = (new OffsetPaginator($dataReader))->withPageSize(30);

        $this->assertSame(5, $paginator->getCurrentPageSize());
        $this->assertSame(5, $paginator->getTotalItems());
    }

    public function testCurrentPageSizeSameTotalItems(): void
    {
        $dataReader = new IterableDataReader(self::DEFAULT_DATASET);
        $paginator = (new OffsetPaginator($dataReader))->withCurrentPage(5);

        $this->assertSame(5, $paginator->getCurrentPageSize());
        $this->assertSame(5, $paginator->getTotalItems());
    }

    public function testGetCurrentPageCannotBeLargerThanMaxPages(): void
    {
        $dataReader = new IterableDataReader(self::DEFAULT_DATASET);
        $paginator = (new OffsetPaginator($dataReader))
            ->withPageSize(2)
            ->withCurrentPage(4);

        $this->assertSame(5, $paginator->getTotalItems());
        $this->assertSame(0, $paginator->getCurrentPageSize());
    }

    public function testEmptyDataSet(): void
    {
        $dataReader = new IterableDataReader([]);
        $paginator = new OffsetPaginator($dataReader);

        $this->assertSame(0, $paginator->getTotalItems());
        $this->assertSame(0, $paginator->getTotalPages());
        $this->assertSame(0, $paginator->getCurrentPageSize());
        $this->assertSame(1, $paginator->getCurrentPage());
        $this->assertTrue($paginator->isOnFirstPage());
        $this->assertTrue($paginator->isOnLastPage());
        $this->assertFalse($paginator->isPaginationRequired());
        $this->assertSame([], $this->iterableToArray($paginator->read()));
    }

    public function testGetSort(): void
    {
        $dataReader = new IterableDataReader([]);
        $paginator = new OffsetPaginator($dataReader);

        $this->assertNull($paginator->getSort());

        $sorting = Sort::only(['id']);

        $dataReader = (new IterableDataReader([['id' => 1], ['id' => 2]]))->withSort($sorting);
        $paginator = new OffsetPaginator($dataReader);

        $this->assertInstanceOf(Sort::class, $paginator->getSort());
    }

    public function testNextPageToken(): void
    {
        $dataReader = new IterableDataReader(self::DEFAULT_DATASET);
        $paginator = (new OffsetPaginator($dataReader))->withToken(PageToken::next('1'));

        $this->assertNull($paginator->getNextToken());

        $paginator = $paginator->withPageSize(2);

        $this->assertPageToken('2', false, $paginator->getNextToken());
    }

    public function testPreviousPageToken(): void
    {
        $dataReader = new IterableDataReader(self::DEFAULT_DATASET);
        $paginator = (new OffsetPaginator($dataReader))->withToken(PageToken::previous('1'));

        $this->assertNull($paginator->getPreviousToken());

        $paginator = $paginator->withToken(PageToken::previous('5'));

        $this->assertPageToken('4', false, $paginator->getPreviousToken());
    }

    public function testImmutability(): void
    {
        $paginator = new OffsetPaginator(new IterableDataReader([]));

        $this->assertNotSame($paginator, $paginator->withToken(PageToken::previous('1')));
        $this->assertNotSame($paginator, $paginator->withPageSize(1));
        $this->assertNotSame($paginator, $paginator->withCurrentPage(1));
    }

    public static function dataIsSupportSorting(): array
    {
        return [
            'IterableDataReader' => [true, new IterableDataReader([])],
            'StubOffsetData' => [false, new StubOffsetData()],
            'StubOffsetDataWithLimit' => [false, (new StubOffsetData())->withLimit(10)],
        ];
    }

    #[DataProvider('dataIsSupportSorting')]
    public function testIsSortable(bool $expected, ReadableDataInterface $reader): void
    {
        $paginator = new OffsetPaginator($reader);

        $this->assertSame($expected, $paginator->isSortable());
    }

    public function testWithSort(): void
    {
        $sort1 = Sort::only(['id'])->withOrderString('id');
        $reader = (new IterableDataReader([]))->withSort($sort1);
        $paginator1 = new OffsetPaginator($reader);

        $sort2 = $sort1->withOrderString('-id');
        $paginator2 = $paginator1->withSort($sort2);

        $this->assertNotSame($paginator1, $paginator2);
        $this->assertSame($sort1, $paginator1->getSort());
        $this->assertSame($sort2, $paginator2->getSort());
    }

    public function testWithSortNonSortableData(): void
    {
        $paginator = new OffsetPaginator(new StubOffsetData());

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Changing sorting is not supported.');
        $paginator->withSort(null);
    }

    public static function dataIsFilterable(): array
    {
        return [
            [true, new IterableDataReader([])],
            [false, new StubOffsetData()],
        ];
    }

    #[DataProvider('dataIsFilterable')]
    public function testIsFilterable(bool $expected, ReadableDataInterface $reader): void
    {
        $paginator = new OffsetPaginator($reader);

        $this->assertSame($expected, $paginator->isFilterable());
    }

    public function testWithFilter(): void
    {
        $reader = (new IterableDataReader([
            'a' => ['id' => 1],
            'b' => ['id' => 2],
        ]));
        $paginator = new OffsetPaginator($reader);

        $paginatorWithFilter = $paginator->withFilter(new Equals('id', 2));

        $this->assertNotSame($paginator, $paginatorWithFilter);
        $this->assertSame(['b' => ['id' => 2]], iterator_to_array($paginatorWithFilter->read()));
    }

    public function testWithFilterNonFilterableData(): void
    {
        $paginator = new OffsetPaginator(new StubOffsetData());

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Changing filtering is not supported.');
        $paginator->withFilter(new Equals('id', 2));
    }

    public function testWithNulledPageToken(): void
    {
        $paginator = (new OffsetPaginator(new StubOffsetData()))->withToken(null);

        $token = $paginator->getToken();

        $this->assertNotNull($token);
        $this->assertSame('1', $token->value);
        $this->assertFalse($token->isPrevious);
    }

    public function testLimitedDataReaderTotalItems(): void
    {
        $dataReader = (new IterableDataReader(self::DEFAULT_DATASET))->withLimit(3);

        $paginator = new OffsetPaginator($dataReader);

        $this->assertSame(3, $paginator->getTotalItems());
    }

    public function testLimitedDataReaderReducedPage(): void
    {
        $dataReader = (new IterableDataReader(self::DEFAULT_DATASET))->withLimit(3);
        $paginator = (new OffsetPaginator($dataReader))
            ->withPageSize(2)
            ->withCurrentPage(2)
        ;

        $count = 0;
        foreach ($paginator->read() as $_item) {
            $count++;
        }

        $this->assertSame(1, $count);
        $this->assertSame(3, $paginator->getTotalItems());
    }

    public function testLimitedDataReaderEqualPage(): void
    {
        $dataReader = (new IterableDataReader(self::DEFAULT_DATASET))->withLimit(4);
        $paginator = (new OffsetPaginator($dataReader))
            ->withPageSize(2)
            ->withCurrentPage(2)
        ;

        $count = 0;
        foreach ($paginator->read() as $_item) {
            $count++;
        }

        $this->assertSame(2, $count);
    }

    public function testReadOneWithLimit0(): void
    {
        $dataReader = (new IterableDataReader(self::DEFAULT_DATASET))->withLimit(0);
        $paginator = new OffsetPaginator($dataReader);

        $this->assertNull($paginator->readOne());
    }

    public function testReadOneWithLimit1(): void
    {
        $dataReader = (new IterableDataReader(self::DEFAULT_DATASET))->withLimit(1);
        $paginator = new OffsetPaginator($dataReader);

        $result = $paginator->readOne();

        $this->assertSame(self::ITEM_1, $result);
    }
}
