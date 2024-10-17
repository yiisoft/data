<?php

declare(strict_types=1);

namespace Yiisoft\Data\Paginator;

use Closure;
use InvalidArgumentException;
use RuntimeException;
use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Data\Reader\Filter\GreaterThan;
use Yiisoft\Data\Reader\Filter\GreaterThanOrEqual;
use Yiisoft\Data\Reader\Filter\LessThan;
use Yiisoft\Data\Reader\Filter\LessThanOrEqual;
use Yiisoft\Data\Reader\FilterableDataInterface;
use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\LimitableDataInterface;
use Yiisoft\Data\Reader\ReadableDataInterface;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Data\Reader\SortableDataInterface;

use function array_reverse;
use function count;
use function key;
use function reset;
use function sprintf;

/**
 * Keyset paginator.
 *
 * Advantages:
 *
 * - Performance doesn't depend on page number
 * - Consistent results regardless of insertions and deletions
 *
 * Disadvantages:
 *
 * - Total number of pages is not available
 * - Can't get to specific page, only "previous" and "next"
 * - Data can't be unordered
 * - The limit set in the data reader leads to an exception
 *
 * @link https://use-the-index-luke.com/no-offset
 *
 * @template TKey as array-key
 * @template TValue as array|object
 *
 * @implements PaginatorInterface<TKey, TValue>
 *
 * @psalm-type FilterCallback = Closure(GreaterThan|LessThan|GreaterThanOrEqual|LessThanOrEqual,KeysetFilterContext):FilterInterface
 */
final class KeysetPaginator implements PaginatorInterface
{
    /**
     * Data reader being paginated.
     *
     * @psalm-var ReadableDataInterface<TKey, TValue>&LimitableDataInterface&FilterableDataInterface&SortableDataInterface
     */
    private ReadableDataInterface $dataReader;

    /**
     * @var int Maximum number of items per page.
     * @psalm-var positive-int
     */
    private int $pageSize = self::DEFAULT_PAGE_SIZE;
    private ?PageToken $token = null;
    private ?string $currentFirstValue = null;
    private ?string $currentLastValue = null;

    /**
     * @var bool Whether there is a previous page.
     */
    private bool $hasPreviousPage = false;

    /**
     * @var bool Whether there is next page.
     */
    private bool $hasNextPage = false;

    /**
     * @psalm-var FilterCallback|null
     */
    private ?Closure $filterCallback = null;

    /**
     * Reader cache against repeated scans.
     * See more {@see __clone()} and {@see initialize()}.
     *
     * @psalm-var null|array<TKey, TValue>
     */
    private ?array $readCache = null;

    /**
     * @param ReadableDataInterface $dataReader Data reader being paginated.
     * @psalm-param ReadableDataInterface<TKey, TValue>&LimitableDataInterface&FilterableDataInterface&SortableDataInterface $dataReader
     * @psalm-suppress DocblockTypeContradiction Needed to allow validating `$dataReader`
     */
    public function __construct(ReadableDataInterface $dataReader)
    {
        if (!$dataReader instanceof FilterableDataInterface) {
            throw new InvalidArgumentException(sprintf(
                'Data reader should implement "%s" to be used with keyset paginator.',
                FilterableDataInterface::class,
            ));
        }

        if (!$dataReader instanceof SortableDataInterface) {
            throw new InvalidArgumentException(sprintf(
                'Data reader should implement "%s" to be used with keyset paginator.',
                SortableDataInterface::class,
            ));
        }

        if (!$dataReader instanceof LimitableDataInterface) {
            throw new InvalidArgumentException(sprintf(
                'Data reader should implement "%s" to be used with keyset paginator.',
                LimitableDataInterface::class,
            ));
        }

        if ($dataReader->getLimit() !== null) {
            throw new InvalidArgumentException('Limited data readers are not supported by keyset pagination.');
        }

        $sort = $dataReader->getSort();

        if ($sort === null) {
            throw new RuntimeException('Data sorting should be configured to work with keyset pagination.');
        }

        if (empty($sort->getOrder())) {
            throw new RuntimeException('Data should be always sorted to work with keyset pagination.');
        }

        $this->dataReader = $dataReader;
    }

    public function __clone()
    {
        $this->readCache = null;
        $this->hasPreviousPage = false;
        $this->hasNextPage = false;
        $this->currentFirstValue = null;
        $this->currentLastValue = null;
    }

    public function withToken(?PageToken $token): static
    {
        $new = clone $this;
        $new->token = $token;
        return $new;
    }

    public function getToken(): ?PageToken
    {
        return $this->token;
    }

    public function withPageSize(int $pageSize): static
    {
        if ($pageSize < 1) {
            throw new InvalidArgumentException('Page size should be at least 1.');
        }

        $new = clone $this;
        $new->pageSize = $pageSize;
        return $new;
    }

    /**
     * Returns a new instance with defined closure for preparing data reader filters.
     *
     * @psalm-param FilterCallback|null $callback Closure with signature:
     *
     * ```php
     * function(
     *    GreaterThan|LessThan|GreaterThanOrEqual|LessThanOrEqual $filter,
     *    KeysetFilterContext $context
     * ): FilterInterface
     * ```
     */
    public function withFilterCallback(?Closure $callback): self
    {
        $new = clone $this;
        $new->filterCallback = $callback;
        return $new;
    }

    /**
     * Reads items of the page.
     *
     * This method uses the read cache to prevent duplicate reads from the data source. See more {@see resetInternal()}.
     */
    public function read(): iterable
    {
        if ($this->readCache !== null) {
            return $this->readCache;
        }

        /** @var Sort $sort */
        $sort = $this->dataReader->getSort();
        /** @infection-ignore-all Any value more than one in the line below will be ignored in `readData()` method */
        $dataReader = $this->dataReader->withLimit($this->pageSize + 1);

        if ($this->token?->isPrevious === true) {
            $sort = $this->reverseSort($sort);
            $dataReader = $dataReader->withSort($sort);
        }

        if ($this->token !== null) {
            $dataReader = $dataReader->withFilter($this->getFilter($sort));
            $this->hasPreviousPage = $this->previousPageExist($dataReader, $sort);
        }

        $data = $this->readData($dataReader, $sort);

        if ($this->token?->isPrevious === true) {
            $data = $this->reverseData($data);
        }

        return $this->readCache = $data;
    }

    public function readOne(): array|object|null
    {
        foreach ($this->read() as $item) {
            return $item;
        }

        return null;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function getCurrentPageSize(): int
    {
        $this->initialize();
        return count($this->readCache);
    }

    public function getPreviousToken(): ?PageToken
    {
        return $this->isOnFirstPage()
            ? null
            : ($this->currentFirstValue === null ? null : PageToken::previous($this->currentFirstValue));
    }

    public function getNextToken(): ?PageToken
    {
        return $this->isOnLastPage()
            ? null
            : ($this->currentLastValue === null ? null : PageToken::next($this->currentLastValue));
    }

    public function isSortable(): bool
    {
        return !($this->dataReader instanceof LimitableDataInterface && $this->dataReader->getLimit() !== null);
    }

    public function withSort(?Sort $sort): static
    {
        $new = clone $this;
        $new->dataReader = $this->dataReader->withSort($sort);
        return $new;
    }

    public function getSort(): ?Sort
    {
        if ($this->dataReader instanceof LimitableDataInterface && $this->dataReader->getLimit() !== null) {
            return null;
        }

        return $this->dataReader->getSort();
    }

    public function isFilterable(): bool
    {
        return true;
    }

    public function withFilter(FilterInterface $filter): static
    {
        $new = clone $this;
        $new->dataReader = $this->dataReader->withFilter($filter);
        return $new;
    }

    public function isOnFirstPage(): bool
    {
        if ($this->token === null) {
            return true;
        }

        $this->initialize();
        return !$this->hasPreviousPage;
    }

    public function isOnLastPage(): bool
    {
        $this->initialize();
        return !$this->hasNextPage;
    }

    public function isPaginationRequired(): bool
    {
        return !$this->isOnFirstPage() || !$this->isOnLastPage();
    }

    /**
     * @psalm-assert array<TKey, TValue> $this->readCache
     */
    private function initialize(): void
    {
        if ($this->readCache !== null) {
            return;
        }

        $cache = [];

        foreach ($this->read() as $key => $value) {
            $cache[$key] = $value;
        }

        $this->readCache = $cache;
    }

    /**
     * @psalm-param ReadableDataInterface<TKey, TValue> $dataReader
     * @psalm-return array<TKey, TValue>
     */
    private function readData(ReadableDataInterface $dataReader, Sort $sort): array
    {
        $data = [];
        [$field] = $this->getFieldAndSortingFromSort($sort);

        foreach ($dataReader->read() as $key => $item) {
            if ($this->currentFirstValue === null) {
                $this->currentFirstValue = (string) ArrayHelper::getValue($item, $field);
            }

            if (count($data) === $this->pageSize) {
                $this->hasNextPage = true;
            } else {
                $this->currentLastValue = (string) ArrayHelper::getValue($item, $field);
                $data[$key] = $item;
            }
        }

        return $data;
    }

    /**
     * @psalm-param array<TKey, TValue> $data
     * @psalm-return array<TKey, TValue>
     */
    private function reverseData(array $data): array
    {
        [$this->currentFirstValue, $this->currentLastValue] = [$this->currentLastValue, $this->currentFirstValue];
        [$this->hasPreviousPage, $this->hasNextPage] = [$this->hasNextPage, $this->hasPreviousPage];
        return array_reverse($data, true);
    }

    /**
     * @psalm-param ReadableDataInterface<TKey, TValue>&LimitableDataInterface&FilterableDataInterface&SortableDataInterface $dataReader
     */
    private function previousPageExist(ReadableDataInterface $dataReader, Sort $sort): bool
    {
        $reverseFilter = $this->getReverseFilter($sort);

        return !empty($dataReader->withFilter($reverseFilter)->readOne());
    }

    private function getFilter(Sort $sort): FilterInterface
    {
        /**
         * @psalm-var PageToken $this->token The code calling this method must ensure that page token is not null.
         */
        $value = $this->token->value;
        [$field, $sorting] = $this->getFieldAndSortingFromSort($sort);

        $filter = $sorting === SORT_ASC ? new GreaterThan($field, $value) : new LessThan($field, $value);
        if ($this->filterCallback === null) {
            return $filter;
        }

        return ($this->filterCallback)(
            $filter,
            new KeysetFilterContext(
                $field,
                $value,
                $sorting,
                false,
            )
        );
    }

    private function getReverseFilter(Sort $sort): FilterInterface
    {
        /**
         * @psalm-var PageToken $this->token The code calling this method must ensure that page token is not null.
         */
        $value = $this->token->value;
        [$field, $sorting] = $this->getFieldAndSortingFromSort($sort);

        $filter = $sorting === SORT_ASC ? new LessThanOrEqual($field, $value) : new GreaterThanOrEqual($field, $value);
        if ($this->filterCallback === null) {
            return $filter;
        }

        return ($this->filterCallback)(
            $filter,
            new KeysetFilterContext(
                $field,
                $value,
                $sorting,
                true,
            )
        );
    }

    private function reverseSort(Sort $sort): Sort
    {
        $order = $sort->getOrder();

        foreach ($order as &$sorting) {
            $sorting = $sorting === 'asc' ? 'desc' : 'asc';
        }

        return $sort->withOrder($order);
    }

    /**
     * @psalm-return array{0: string, 1: int}
     */
    private function getFieldAndSortingFromSort(Sort $sort): array
    {
        $order = $sort->getOrder();

        return [
            (string) key($order),
            reset($order) === 'asc' ? SORT_ASC : SORT_DESC,
        ];
    }
}
