<?php
declare(strict_types=1);

namespace Yiisoft\Data\Paginator;

use Yiisoft\Data\Reader\Filter\GreaterThan;
use Yiisoft\Data\Reader\Filter\LessThan;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\FilterableDataInterface;
use Yiisoft\Data\Reader\Sort;
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
     * @var FilterableDataInterface|DataReaderInterface
     */
    private $dataReader;
    private $pageSize;

    private $lastValue;

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
        $dataReader = $this->dataReader->withLimit($this->pageSize);

        if (isset($this->lastValue)) {
            $order = $this->dataReader->getSort()->getOrder();

            if ($order === []) {
                throw new \RuntimeException('Data should be always sorted in order to work with keyset pagination');
            }

            // first order field is the field we are paging by
            foreach ($order as $field => $sorting) {
                break;
            }

            if ($sorting === 'asc') {
                $filter = new GreaterThan($field, $this->lastValue);
            } elseif ($sorting === 'desc') {
                $filter = new LessThan($field, $this->lastValue);
            }

            $dataReader = $dataReader->withFilter($filter);
        }

        yield from $dataReader->read();
    }

    public function withLast($value): self
    {
        $new = clone $this;
        $new->lastValue = $value;
        return $new;
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
