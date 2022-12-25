<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Paginator;

use InvalidArgumentException;
use Yiisoft\Data\Paginator\OffsetPaginator;
use Yiisoft\Data\Paginator\PaginatorException;
use Yiisoft\Data\Reader\CountableDataInterface;
use Yiisoft\Data\Reader\Iterable\IterableDataReader;
use Yiisoft\Data\Reader\OffsetableDataInterface;
use Yiisoft\Data\Reader\ReadableDataInterface;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Data\Tests\TestCase;

final class OffsetPaginatorTest extends TestCase
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

    public function testDataReaderWithoutOffsetableInterface(): void
    {
        $nonOffsetableDataReader = new class () implements ReadableDataInterface, CountableDataInterface {
            public function withLimit(int $limit): static
            {
                // do nothing
            }

            public function read(): iterable
            {
                return [];
            }

            public function readOne()
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
            }

            public function read(): iterable
            {
                return [];
            }

            public function readOne()
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
            }
        };

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Data reader should implement "%s" in order to be used with offset paginator.',
            CountableDataInterface::class,
        ));

        new OffsetPaginator($nonCountableDataReader);
    }

    public function testDefaultState(): void
    {
        $dataReader = new IterableDataReader(self::DEFAULT_DATASET);
        $paginator = new OffsetPaginator($dataReader);

        $this->assertSame(0, $paginator->getOffset());
        $this->assertSame(1, $paginator->getCurrentPage());
        $this->assertTrue($paginator->isOnFirstPage());
        $this->assertFalse($paginator->isRequired());
    }

    public function testIsRequired(): void
    {
        $dataReader = new IterableDataReader(self::DEFAULT_DATASET);
        $paginator = (new OffsetPaginator($dataReader))->withPageSize(2);

        $this->assertTrue($paginator->isRequired());
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

        $this->expectException(PaginatorException::class);
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
        $this->expectException(PaginatorException::class);
        $this->expectExceptionMessage('Page not found.');

        $this->iterableToArray($paginator->read());
    }

    public function testWithPageSize(): void
    {
        $dataReader = new IterableDataReader(self::DEFAULT_DATASET);
        $paginator = new OffsetPaginator($dataReader);
        $newPaginator = $paginator->withPageSize(125);

        $this->assertSame(OffsetPaginator::DEFAULT_PAGE_SIZE, $paginator->getPageSize());
        $this->assertSame(125, $newPaginator->getPageSize());
    }

    public function testPageSizeCannotBeLessThanOne(): void
    {
        $dataReader = new IterableDataReader(self::DEFAULT_DATASET);
        $paginator = new OffsetPaginator($dataReader);

        $this->expectException(PaginatorException::class);
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

    public function testIsLastPageBeyondMaxPages(): void
    {
        $dataReader = new IterableDataReader(self::DEFAULT_DATASET);
        $paginator = (new OffsetPaginator($dataReader))
            ->withPageSize(2)
            ->withCurrentPage(4)
        ;

        $this->assertSame(3, $paginator->getTotalPages());
        $this->expectException(PaginatorException::class);
        $this->expectExceptionMessage('Page not found.');

        $paginator->isOnLastPage();
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
            ->withCurrentPage(4)
        ;

        $this->assertSame(5, $paginator->getTotalItems());
        $this->expectException(PaginatorException::class);
        $this->expectExceptionMessage('Page not found.');

        $paginator->getCurrentPageSize();
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
        $this->assertFalse($paginator->isRequired());
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
        $paginator = (new OffsetPaginator($dataReader))->withNextPageToken('1');

        $this->assertNull($paginator->getNextPageToken());

        $paginator = $paginator->withPageSize(2);

        $this->assertSame('2', $paginator->getNextPageToken());
    }

    public function testPreviousPageToken(): void
    {
        $dataReader = new IterableDataReader(self::DEFAULT_DATASET);
        $paginator = (new OffsetPaginator($dataReader))->withPreviousPageToken('1');

        $this->assertNull($paginator->getPreviousPageToken());

        $paginator = $paginator->withPreviousPageToken('5');

        $this->assertSame('4', $paginator->getPreviousPageToken());
    }

    public function testImmutability(): void
    {
        $paginator = new OffsetPaginator(new IterableDataReader([]));

        $this->assertNotSame($paginator, $paginator->withNextPageToken('1'));
        $this->assertNotSame($paginator, $paginator->withPreviousPageToken('1'));
        $this->assertNotSame($paginator, $paginator->withPageSize(1));
        $this->assertNotSame($paginator, $paginator->withCurrentPage(1));
    }
}
