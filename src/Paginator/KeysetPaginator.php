<?php

declare(strict_types=1);

namespace Yiisoft\Data\Paginator;

use InvalidArgumentException;
use RuntimeException;
use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Data\Reader\Filter\CompareFilter;
use Yiisoft\Data\Reader\Filter\GreaterThan;
use Yiisoft\Data\Reader\Filter\GreaterThanOrEqual;
use Yiisoft\Data\Reader\Filter\LessThan;
use Yiisoft\Data\Reader\Filter\LessThanOrEqual;
use Yiisoft\Data\Reader\FilterableDataInterface;
use Yiisoft\Data\Reader\ReadableDataInterface;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Data\Reader\SortableDataInterface;

use function count;
use function is_callable;
use function is_object;

/**
 * Keyset paginator
 *
 * - Equally fast for 1st and 1000nd page
 * - Total number of pages is not available
 * - Cannot get to specific page, only "next" and "previous"
 *
 * @link https://use-the-index-luke.com/no-offset
 *
 * @psalm-template DataReaderType = ReadableDataInterface<TKey, TValue>&FilterableDataInterface&SortableDataInterface
 *
 * @template TKey as array-key
 * @template TValue
 *
 * @implements PaginatorInterface<TKey, TValue>
 */
class KeysetPaginator implements PaginatorInterface
{
    /**
     * @var DataReaderType
     */
    private ReadableDataInterface $dataReader;
    private int $pageSize = self::DEFAULT_PAGE_SIZE;
    private ?string $firstValue = null;
    private ?string $lastValue = null;

    /**
     * @var mixed
     */
    private $currentFirstValue;

    /**
     * @var mixed
     */
    private $currentLastValue;

    /**
     * @var bool Previous page has item indicator.
     */
    private bool $hasPreviousPageItem = false;
    /**
     * @var bool Next page has item indicator.
     */
    private bool $hasNextPageItem = false;

    /**
     * Reader cache against repeated scans.
     * See more {@see __clone()} and {@see initializeInternal()}.
     *
     * @psalm-var array<TKey, TValue>|null
     */
    private ?array $readCache = null;

    /**
     * @param DataReaderType $dataReader
     */
    public function __construct(ReadableDataInterface $dataReader)
    {
        if (!$dataReader instanceof FilterableDataInterface) {
            throw new InvalidArgumentException(
                'Data reader should implement FilterableDataInterface to be used with keyset paginator.'
            );
        }

        if (!$dataReader instanceof SortableDataInterface) {
            throw new InvalidArgumentException(
                'Data reader should implement SortableDataInterface to be used with keyset paginator.'
            );
        }

        if ($dataReader->getSort() === null) {
            throw new RuntimeException('Data sorting should be configured to work with keyset pagination.');
        }

        /** @psalm-suppress PossiblyNullReference */
        if ($dataReader->getSort()->getOrder() === []) {
            throw new RuntimeException('Data should be always sorted to work with keyset pagination.');
        }

        $this->dataReader = $dataReader;
    }

    /**
     * Reads items of the page
     *
     * This method uses the read cache to prevent duplicate reads from the data source. See more {@see resetInternal()}
     */
    public function read(): iterable
    {
        if ($this->readCache !== null) {
            return $this->readCache;
        }

        $dataReader = $this->dataReader->withLimit($this->pageSize + 1);
        $sort = $this->dataReader->getSort();

        if ($this->isGoingToPreviousPage()) {
            $sort = $this->reverseSort($sort);
            $dataReader = $dataReader->withSort($sort);
        }

        if ($this->isGoingSomewhere()) {
            $dataReader = $dataReader->withFilter($this->getFilter($sort));
            $this->hasPreviousPageItem = $this->previousPageExist($dataReader, $sort);
        }

        $data = $this->readData($dataReader, $sort);
        if ($this->isGoingToPreviousPage()) {
            $data = $this->reverseData($data);
        }

        /** @psalm-var array<TKey, TValue> $data */
        return $this->readCache = $data;
    }

    /**
     * @return $this
     *
     * @psalm-mutation-free
     */
    public function withPreviousPageToken(?string $token): self
    {
        $new = clone $this;
        $new->firstValue = $token;
        $new->lastValue = null;
        return $new;
    }

    /**
     * @return $this
     *
     * @psalm-mutation-free
     */
    public function withNextPageToken(?string $token): self
    {
        $new = clone $this;
        $new->firstValue = null;
        $new->lastValue = $token;
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

    /**
     * @return $this
     *
     * @psalm-mutation-free
     */
    public function withPageSize(int $pageSize): self
    {
        if ($pageSize < 1) {
            throw new InvalidArgumentException('Page size should be at least 1.');
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

    /**
     * @psalm-assert array<TKey, TValue> $this->readCache
     */
    protected function initializeInternal(): void
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

    public function isRequired(): bool
    {
        return !$this->isOnFirstPage() || !$this->isOnLastPage();
    }

    /**
     * @psalm-assert-if-true string $this->firstValue
     * @psalm-assert-if-true null $this->lastValue
     */
    private function isGoingToPreviousPage(): bool
    {
        return $this->firstValue !== null && $this->lastValue === null;
    }

    private function isGoingSomewhere(): bool
    {
        return $this->firstValue !== null || $this->lastValue !== null;
    }

    private function getFilter(Sort $sort): CompareFilter
    {
        [$field, $sorting] = $this->getFieldAndSortingFromSort($sort);
        if ($sorting === 'asc') {
            return new GreaterThan($field, $this->getValue());
        }
        return new LessThan($field, $this->getValue());
    }

    private function getReverseFilter(Sort $sort): CompareFilter
    {
        [$field, $sorting] = $this->getFieldAndSortingFromSort($sort);
        if ($sorting === 'asc') {
            return new LessThanOrEqual($field, $this->getValue());
        }
        return new GreaterThanOrEqual($field, $this->getValue());
    }

    private function getValue(): ?string
    {
        return $this->isGoingToPreviousPage() ? $this->firstValue : $this->lastValue;
    }

    private function reverseSort(Sort $sort): Sort
    {
        $order = $sort->getOrder();
        foreach ($order as &$sorting) {
            $sorting = $sorting === 'asc' ? 'desc' : 'asc';
        }

        return $sort->withOrder($order);
    }

    private function getFieldAndSortingFromSort(Sort $sort): array
    {
        $order = $sort->getOrder();

        return [
            key($order),
            reset($order),
        ];
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
                $this->currentFirstValue = $this->getValueFromItem($item, $field);
            }
            if (count($data) === $this->pageSize) {
                $this->hasNextPageItem = true;
            } else {
                $this->currentLastValue = $this->getValueFromItem($item, $field);
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
        [$this->hasPreviousPageItem, $this->hasNextPageItem] = [$this->hasNextPageItem, $this->hasPreviousPageItem];
        return array_reverse($data, true);
    }

    /**
     * @psalm-param DataReaderType $dataReader
     */
    private function previousPageExist(ReadableDataInterface $dataReader, Sort $sort): bool
    {
        $reverseFilter = $this->getReverseFilter($sort);
        foreach ($dataReader->withFilter($reverseFilter)->withLimit(1)->read() as $void) {
            return true;
        }
        return false;
    }

    /**
     * @param mixed $item
     *
     * @return mixed
     */
    private function getValueFromItem($item, string $field)
    {
        $methodName = 'get' . ucfirst($field);
        if (is_object($item) && is_callable([$item, $methodName])) {
            return $item->$methodName();
        }

        return ArrayHelper::getValue($item, $field);
    }

    public function getSort(): ?Sort
    {
        return $this->dataReader->getSort();
    }
}
