<?php

declare(strict_types=1);

namespace Yiisoft\Data\Paginator;

use Yiisoft\Data\Reader\Filter\GreaterThan;
use Yiisoft\Data\Reader\Filter\GreaterThanOrEqual;
use Yiisoft\Data\Reader\Filter\LessThan;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Filter\LessThanOrEqual;
use Yiisoft\Data\Reader\FilterableDataInterface;
use Yiisoft\Data\Reader\SortableDataInterface;

/**
 * Keyset paginator
 *
 * - Equally fast for 1st and 1000nd page
 * - Total number of pages is not available
 * - Cannot get to specific page, only "next" and "previous"
 *
 * @link https://use-the-index-luke.com/no-offset
 */
class KeysetPaginator implements PaginatorInterface
{
    /**
     * @var FilterableDataInterface|DataReaderInterface|SortableDataInterface
     */
    private $dataReader;
    /**
     * @var int
     */
    private $pageSize = self::DEFAULT_PAGE_SIZE;

    private $firstValue;
    private $lastValue;

    private $currentFirstValue;
    private $currentLastValue;

    /**
     * @var bool Previous page has item indicator.
     */
    private $hasPreviousPageItem = false;
    /**
     * @var bool Next page has item indicator.
     */
    private $hasNextPageItem = false;

    /**
     * @var array|null Reader cache against repeated scans.
     *
     * See more {@see __clone()} and {@see initializeInternal()}.
     */
    private $readCache;

    public function __construct(DataReaderInterface $dataReader)
    {
        if (!$dataReader instanceof FilterableDataInterface) {
            throw new \InvalidArgumentException('Data reader should implement FilterableDataInterface in order to be used with keyset paginator');
        }

        if (!$dataReader instanceof SortableDataInterface) {
            throw new \InvalidArgumentException('Data reader should implement SortableDataInterface in order to be used with keyset paginator');
        }

        if ($dataReader->getSort() === null) {
            throw new \RuntimeException('Data sorting should be configured in order to work with keyset pagination');
        }

        $this->dataReader = $dataReader;
    }

    /**
     * Reads items of the page
     *
     * This method uses the read cache to prevent duplicate reads from the data source. See more {@see resetInternal()}
     *
     * @return iterable
     */
    public function read(): iterable
    {
        if ($this->readCache) {
            return $this->readCache;
        }

        $dataReader = $this->dataReader->withLimit($this->pageSize + 1);

        $sort = $this->dataReader->getSort();
        $order = $sort->getOrder();

        if ($order === []) {
            throw new \RuntimeException('Data should be always sorted in order to work with keyset pagination');
        }

        $goingToPreviousPage = $this->firstValue !== null && $this->lastValue === null;
        $goingToNextPage = $this->firstValue === null && $this->lastValue !== null;

        if ($goingToPreviousPage) {
            // reverse sorting
            foreach ($order as &$sorting) {
                $sorting = $sorting === 'asc' ? 'desc' : 'asc';
            }
            unset($sorting);
            $dataReader = $dataReader->withSort($sort->withOrder($order));
        }

        // first order field is the field we are paging by
        $field = null;
        $sorting = null;
        foreach ($order as $field => $sorting) {
            break;
        }

        if ($goingToPreviousPage || $goingToNextPage) {
            $value = $goingToPreviousPage ? $this->firstValue : $this->lastValue;

            $filter = null;
            $reverseFilter = null;
            if ($sorting === 'asc') {
                $filter = new GreaterThan($field, $value);
                $reverseFilter = new LessThanOrEqual($field, $value);
            } else {
                $filter = new LessThan($field, $value);
                $reverseFilter = new GreaterThanOrEqual($field, $value);
            }

            $dataReader = $dataReader->withFilter($filter);
            foreach ($dataReader->withFilter($reverseFilter)->withLimit(1)->read() as $void) {
                $this->hasPreviousPageItem = true;
            }
        }

        $data = [];
        foreach ($dataReader->read() as $item) {
            if ($this->currentFirstValue === null) {
                $this->currentFirstValue = $item[$field];
            }
            if (count($data) === $this->pageSize) {
                $this->hasNextPageItem = true;
            } else {
                $this->currentLastValue = $item[$field];
                $data[] = $item;
            }
        }

        if ($goingToPreviousPage) {
            [$this->currentFirstValue, $this->currentLastValue] = [$this->currentLastValue, $this->currentFirstValue];
            [$this->hasPreviousPageItem, $this->hasNextPageItem] = [$this->hasNextPageItem, $this->hasPreviousPageItem];
            $data = array_reverse($data);
        }

        return $this->readCache = $data;
    }

    public function withPreviousPageToken(?string $value): self
    {
        $new = clone $this;
        $new->firstValue = $value;
        $new->lastValue = null;
        return $new;
    }

    public function withNextPageToken(?string $value): self
    {
        $new = clone $this;
        $new->firstValue = null;
        $new->lastValue = $value;
        return $new;
    }

    public function getPreviousPageToken(): ?string
    {
        if ($this->isOnFirstPage()) {
            return null;
        }
        return (string)$this->currentFirstValue;
    }

    public function getNextPageToken(): ?string
    {
        if ($this->isOnLastPage()) {
            return null;
        }
        return (string)$this->currentLastValue;
    }

    public function withPageSize(int $pageSize): self
    {
        if ($pageSize < 1) {
            throw new \InvalidArgumentException('Page size should be at least 1');
        }

        $new = clone $this;
        $new->pageSize = $pageSize;
        return $new;
    }

    public function isOnLastPage(): bool
    {
        $this->initializeInternal();
        return !$this->hasNextPageItem;
    }

    public function isOnFirstPage(): bool
    {
        if ($this->lastValue === null && $this->firstValue === null) {
            // Initial state, no values.
            return true;
        }
        $this->initializeInternal();
        return !$this->hasPreviousPageItem;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function getCurrentPageSize(): int
    {
        $this->initializeInternal();
        return count($this->readCache);
    }

    public function __clone()
    {
        $this->readCache = null;
        $this->hasPreviousPageItem = false;
        $this->hasNextPageItem = false;
        $this->currentFirstValue = null;
        $this->currentLastValue = null;
    }

    protected function initializeInternal(): void
    {
        if ($this->readCache !== null) {
            return;
        }
        $cache = [];
        foreach ($this->read() as $value) {
            $cache[] = $value;
        }
        $this->readCache = $cache;
    }

    public function isRequired(): bool
    {
        return !$this->isOnFirstPage() || !$this->isOnLastPage();
    }
}
