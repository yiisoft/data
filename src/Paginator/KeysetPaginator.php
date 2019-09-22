<?php
declare(strict_types=1);

namespace Yiisoft\Data\Paginator;

use Yiisoft\Data\Reader\Filter\GreaterThan;
use Yiisoft\Data\Reader\Filter\LessThan;
use Yiisoft\Data\Reader\DataReaderInterface;
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
     * @var iterable|null Reader cache against repeated scans.
     *
     * See more {@see resetReadCache()} and {@see initReadCache()}.
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
     * This method uses the read cache to prevent duplicate reads from the data source. See more {@see resetReadCache()}
     *
     * @return iterable
     */
    public function read(): iterable
    {
        if ($this->readCache) {
            return $this->readCache;
        }
        $this->currentLastValue = null;
        $this->currentFirstValue = null;

        $dataReader = $this->dataReader->withLimit($this->pageSize);

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
            if ($sorting === 'asc') {
                $filter = new GreaterThan($field, $value);
            } elseif ($sorting === 'desc') {
                $filter = new LessThan($field, $value);
            }

            $dataReader = $dataReader->withFilter($filter);
        }

        $data = [];
        foreach ($dataReader->read() as $item) {
            $this->currentLastValue = $item[$field];
            if ($this->currentFirstValue === null) {
                $this->currentFirstValue = $item[$field];
            }
            $data[] = $item;
        }

        if ($goingToPreviousPage) {
            [$this->currentFirstValue, $this->currentLastValue] = [$this->currentLastValue, $this->currentFirstValue];
            $data = array_reverse($data);
        }

        return $this->readCache = $data;
    }

    public function withPreviousPageToken($value): PaginatorInterface
    {
        $new = clone $this;
        $new->firstValue = $value;
        $new->lastValue = null;
        $new->resetReadCache();
        return $new;
    }

    public function withNextPageToken($value): PaginatorInterface
    {
        $new = clone $this;
        $new->firstValue = null;
        $new->lastValue = $value;
        $new->resetReadCache();
        return $new;
    }

    /**
     * Token for the previous page.
     *
     * The token of the previous page may be available even if the return value of {@see isOnFirstPage()} is true.
     * This method allows to continue paging when a new record is created.
     *
     * @return string|null
     */
    public function getPreviousPageToken(): ?string
    {
        $this->initReadCache();
        return (string)($this->currentFirstValue ?? $this->firstValue);
    }

    /**
     * Token for the next page.
     *
     * The token of the next page may be available even if the return value of {@see isOnLastPage()} is true.
     * This method allows to continue paging when a new record is created.
     *
     * @return string|null
     */
    public function getNextPageToken(): ?string
    {
        $this->initReadCache();
        return (string)($this->currentLastValue ?? $this->lastValue);
    }

    public function withPageSize(int $pageSize): PaginatorInterface
    {
        if ($pageSize < 1) {
            throw new \InvalidArgumentException('Page size should be at least 1');
        }

        $new = clone $this;
        $new->pageSize = $pageSize;
        $new->resetReadCache();
        return $new;
    }

    public function isOnLastPage(): bool
    {
        return !$this->isOnFirstPage() && $this->getCurrentPageSize() !== $this->pageSize;
    }

    public function isOnFirstPage(): bool
    {
        if ($this->getCurrentPageSize() < $this->pageSize && $this->firstValue !== null) {
            // The page size is smaller than the specified size and goes to the previous page.
            return true;
        }
        if ($this->lastValue === null && $this->firstValue === null) {
            // Initial state, no values.
            return true;
        }
        return false;
    }

    public function getCurrentPageSize(): int
    {
        $this->initReadCache();
        return count($this->readCache);
    }

    /**
     * Reset the read cache
     *
     * Properties of this object using the read cache are to prevent duplicate reads. However,
     * for these properties to work properly after changing the parameters, it is need to clear the cache.
     * Therefore, it is important that you call this method if you change the default parameters.
     */
    protected function resetReadCache(): void
    {
        $this->readCache = null;
    }

    /**
     * Initializes the reading cache
     */
    protected function initReadCache(): void
    {
        if($this->readCache !== null) {
            return;
        }
        $data = $this->read();
        if ($data instanceof \Traversable && !($data instanceof \Countable)) {
            $data = iterator_to_array($data);
        }
        $this->readCache = $data;
    }
}
