<?php

declare(strict_types=1);

namespace Yiisoft\Data\Paginator;

use Generator;
use InvalidArgumentException;
use LogicException;
use Yiisoft\Data\Reader\CountableDataInterface;
use Yiisoft\Data\Reader\FilterableDataInterface;
use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\LimitableDataInterface;
use Yiisoft\Data\Reader\OffsetableDataInterface;
use Yiisoft\Data\Reader\ReadableDataInterface;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Data\Reader\SortableDataInterface;

use function ceil;
use function max;
use function sprintf;

/**
 * Offset paginator.
 *
 * Advantages:
 *
 * - Total number of pages is available
 * - Can get to specific page
 * - Data can be unordered
 * - The limit set in the data reader is taken into account
 *
 * Disadvantages:
 *
 * - Performance degrades with page number increase
 * - Insertions or deletions in the middle of the data are making results inconsistent
 *
 * @template TKey as array-key
 * @template TValue as array|object
 *
 * @implements PaginatorInterface<TKey, TValue>
 */
final class OffsetPaginator implements PaginatorInterface
{
    /**
     * @var PageToken Current page token
     */
    private PageToken $token;

    /**
     * @var int Maximum number of items per page.
     * @psalm-var positive-int
     */
    private int $pageSize = self::DEFAULT_PAGE_SIZE;

    /**
     * Data reader being paginated.
     *
     * @psalm-var ReadableDataInterface<TKey, TValue>&LimitableDataInterface<TKey, TValue>&OffsetableDataInterface<TKey, TValue>&CountableDataInterface
     */
    private ReadableDataInterface $dataReader;

    /**
     * @param ReadableDataInterface $dataReader Data reader being paginated.
     * @psalm-param ReadableDataInterface<TKey, TValue>&LimitableDataInterface&OffsetableDataInterface&CountableDataInterface $dataReader
     * @psalm-suppress DocblockTypeContradiction Needed to allow validating `$dataReader`
     */
    public function __construct(ReadableDataInterface $dataReader)
    {
        if (!$dataReader instanceof OffsetableDataInterface) {
            throw new InvalidArgumentException(sprintf(
                'Data reader should implement "%s" in order to be used with offset paginator.',
                OffsetableDataInterface::class,
            ));
        }

        if (!$dataReader instanceof CountableDataInterface) {
            throw new InvalidArgumentException(sprintf(
                'Data reader should implement "%s" in order to be used with offset paginator.',
                CountableDataInterface::class,
            ));
        }

        if (!$dataReader instanceof LimitableDataInterface) {
            throw new InvalidArgumentException(sprintf(
                'Data reader should implement "%s" in order to be used with offset paginator.',
                LimitableDataInterface::class,
            ));
        }

        $this->dataReader = $dataReader;
        $this->token = PageToken::next('1');
    }

    public function withToken(?PageToken $token): static
    {
        if ($token === null) {
            $page = 1;
        } else {
            $page = (int) $token->value;
            if ($page < 1) {
                throw new InvalidPageException('Current page should be at least 1.');
            }
        }

        return $this->withCurrentPage($page);
    }

    public function withPageSize(int $pageSize): static
    {
        /** @psalm-suppress DocblockTypeContradiction We don't believe in psalm types */
        if ($pageSize < 1) {
            throw new InvalidArgumentException('Page size should be at least 1.');
        }

        $new = clone $this;
        $new->pageSize = $pageSize;
        return $new;
    }

    /**
     * Get a new instance with the given current page number set.
     *
     * @param int $page Page number.
     *
     * @throws InvalidArgumentException If page is not a positive number.
     *
     * @return self New instance.
     *
     * @psalm-param positive-int $page
     */
    public function withCurrentPage(int $page): self
    {
        /** @psalm-suppress DocblockTypeContradiction */
        if ($page < 1) {
            throw new InvalidArgumentException('Current page should be at least 1.');
        }

        $new = clone $this;
        $new->token = PageToken::next((string) $page);
        return $new;
    }

    public function getToken(): PageToken
    {
        return $this->token;
    }

    public function getNextToken(): ?PageToken
    {
        return $this->isOnLastPage() ? null : PageToken::next((string) ($this->getCurrentPage() + 1));
    }

    public function getPreviousToken(): ?PageToken
    {
        return $this->isOnFirstPage() ? null : PageToken::next((string) ($this->getCurrentPage() - 1));
    }

    public function nextPage(): ?static
    {
        $nextToken = $this->getNextToken();
        return $nextToken === null
            ? null
            : $this->withToken($nextToken);
    }

    public function previousPage(): ?static
    {
        $previousToken = $this->getPreviousToken();
        return $previousToken === null
            ? null
            : $this->withToken($previousToken);
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    /**
     * Get the current page number.
     *
     * @return int Current page number.
     * @psalm-return positive-int
     */
    public function getCurrentPage(): int
    {
        /** @var positive-int */
        return (int) $this->token->value;
    }

    public function getCurrentPageSize(): int
    {
        $pages = $this->getInternalTotalPages();

        if ($pages === 1) {
            return $this->getTotalItems();
        }

        $currentPage = $this->getCurrentPage();

        if ($currentPage < $pages) {
            return $this->pageSize;
        }

        if ($currentPage === $pages) {
            /** @psalm-var positive-int Because the total items number is more than offset */
            return $this->getTotalItems() - $this->getOffset();
        }

        return 0;
    }

    /**
     * Get offset for the current page, that is the number of items to skip before the current page is reached.
     *
     * @return int Offset.
     */
    public function getOffset(): int
    {
        return $this->pageSize * ($this->getCurrentPage() - 1);
    }

    /**
     * Get total number of items in the whole data reader being paginated.
     *
     * @return int Total items number.
     *
     * @psalm-return non-negative-int
     */
    public function getTotalItems(): int
    {
        $count = $this->dataReader->count();

        $dataReaderLimit = $this->dataReader->getLimit();
        if ($dataReaderLimit !== null && $count > $dataReaderLimit) {
            return $dataReaderLimit;
        }

        return $count;
    }

    /**
     * Get total number of pages in a data reader being paginated.
     *
     * @return int Total pages number.
     */
    public function getTotalPages(): int
    {
        return (int) ceil($this->getTotalItems() / $this->pageSize);
    }

    /**
     * @psalm-assert-if-true SortableDataInterface $this->dataReader
     */
    public function isSortable(): bool
    {
        if ($this->dataReader instanceof LimitableDataInterface && $this->dataReader->getLimit() !== null) {
            return false;
        }

        return $this->dataReader instanceof SortableDataInterface;
    }

    public function withSort(?Sort $sort): static
    {
        if (!$this->isSortable()) {
            throw new LogicException('Changing sorting is not supported.');
        }

        $new = clone $this;
        $new->dataReader = $this->dataReader->withSort($sort);
        return $new;
    }

    public function getSort(): ?Sort
    {
        return $this->dataReader instanceof SortableDataInterface ? $this->dataReader->getSort() : null;
    }

    /**
     * @psalm-assert-if-true FilterableDataInterface $this->dataReader
     */
    public function isFilterable(): bool
    {
        return $this->dataReader instanceof FilterableDataInterface;
    }

    public function withFilter(FilterInterface $filter): static
    {
        if (!$this->isFilterable()) {
            throw new LogicException('Changing filtering is not supported.');
        }

        $new = clone $this;
        $new->dataReader = $this->dataReader->withFilter($filter);
        return $new;
    }

    /**
     * @psalm-return Generator<TKey, TValue, mixed, null>
     */
    public function read(): iterable
    {
        $currentPage = $this->getCurrentPage();
        if ($currentPage > $this->getInternalTotalPages()) {
            throw new PageNotFoundException($currentPage);
        }

        $limit = $this->pageSize;
        $dataReaderLimit = $this->dataReader->getLimit();

        if ($dataReaderLimit !== null && ($this->getOffset() + $this->pageSize) > $dataReaderLimit) {
            /** @psalm-var non-negative-int $limit */
            $limit = $dataReaderLimit - $this->getOffset();
        }

        yield from $this->dataReader
            ->withLimit($limit)
            ->withOffset($this->getOffset())
            ->read();
    }

    public function readOne(): array|object|null
    {
        $limit = 1;

        $dataReaderLimit = $this->dataReader->getLimit();
        if ($dataReaderLimit !== null && ($this->getOffset() + 1) > $dataReaderLimit) {
            $limit = 0;
        }

        return $this->dataReader
            ->withLimit($limit)
            ->withOffset($this->getOffset())
            ->readOne();
    }

    public function isOnFirstPage(): bool
    {
        return $this->token->value === '1';
    }

    public function isOnLastPage(): bool
    {
        return $this->getCurrentPage() >= $this->getInternalTotalPages();
    }

    public function isPaginationRequired(): bool
    {
        return $this->getTotalPages() > 1;
    }

    /**
     * @psalm-return positive-int
     */
    private function getInternalTotalPages(): int
    {
        return max(1, $this->getTotalPages());
    }
}
