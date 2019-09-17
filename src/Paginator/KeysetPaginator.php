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
class KeysetPaginator
{
    /**
     * @var FilterableDataInterface|DataReaderInterface|SortableDataInterface
     */
    private $dataReader;
    private $pageSize;

    private $lastValue;
    private $firstValue;
    private $currentLastValue;
    private $currentFirstValue;

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

    public function read(): iterable
    {
        $this->currentLastValue = null;
        $this->currentFirstValue = null;
        $goingToPreviousPage = $this->firstValue !== null && $this->lastValue === null;
        $dataReader = $this->dataReader->withLimit($this->pageSize);

        $order = $this->dataReader->getSort()->getOrder();

        if ($order === []) {
            throw new \RuntimeException('Data should be always sorted in order to work with keyset pagination');
        }

        // first order field is the field we are paging by
        foreach ($order as $field => $sorting) {
            break;
        }

        if($goingToPreviousPage) {
            // reverse sorting
            foreach ($order as &$sorting) {
                $sorting = $sorting === 'asc' ? 'desc' : 'asc';
            }
            $dataReader = $dataReader->withSort($dataReader->getSort()->withOrder($order));
        }

        if ((isset($this->lastValue) && !$goingToPreviousPage) || (isset($this->firstValue) && $goingToPreviousPage)) {
            $value = $goingToPreviousPage ? $this->firstValue : $this->lastValue;
            if ($sorting === 'asc') {
                $filter = new GreaterThan($field, $value);
            } elseif ($sorting === 'desc') {
                $filter = new LessThan($field, $value);
            }

            $dataReader = $dataReader->withFilter($filter);
        }

        $data = [];
        foreach($dataReader->read() as $item) {
            $this->currentLastValue = $item[$field];
            if($this->currentFirstValue === null) {
                $this->currentFirstValue = $item[$field];
            }
            $data[] = $item;
        }

        if($goingToPreviousPage) {
            [$this->currentFirstValue, $this->currentLastValue] = [$this->currentLastValue, $this->currentFirstValue];
            return array_reverse($data);
        }

        return $data;
    }

    public function withLast($value): self
    {
        $new = clone $this;
        $new->lastValue = $value;
        return $new;
    }

    public function withFirst($value): self
    {
        $new = clone $this;
        $new->firstValue = $value;
        return $new;
    }

    public function getLast() {
        return $this->currentLastValue;
    }

    public function getFirst() {
        return $this->currentFirstValue;
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
}
