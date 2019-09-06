<?php
namespace Yiisoft\Data\Paginator;

use Yiisoft\Data\Reader\Criterion\GreaterThan;
use Yiisoft\Data\Reader\Criterion\LessThan;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Filter;
use Yiisoft\Data\Reader\FilterableDataInterface;
use Yiisoft\Data\Reader\Sort;

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

    private $lastField;
    private $lastValue;

    public function __construct(DataReaderInterface $dataReader)
    {
        if (!$dataReader instanceof FilterableDataInterface) {
            throw new \InvalidArgumentException('Data reader should implement FilterableDataInterface in order to be used with keyset paginator');
        }

        $this->dataReader = $dataReader;
    }

    public function read(): iterable
    {
        $dataReader = $this->dataReader->withLimit($this->pageSize);

        if (isset($this->lastField, $this->lastValue)) {
            /** @var Sort $sort */
            $sort = $this->dataReader->getSort();
            $order = $sort->getOrder();

            $sorting = $order[$this->lastField] ?? null;
            if ($sorting === SORT_ASC) {
                $criteria = new GreaterThan($this->lastField, $this->lastValue);
            } elseif ($sorting === SORT_DESC) {
                $criteria = new LessThan($this->lastField, $this->lastValue);
            }

            // TODO: what to do if we're not aware of sorting?

            $dataReader = $dataReader->withFilter(new Filter($criteria->toArray()));
        }

        yield from $dataReader->read();
    }

    public function withLast(string $field, $value): self
    {
        $new = clone $this;
        $new->lastField = $field;
        $new->lastValue = $value;
        return $new;
    }

    public function withPageSize(int $pageSize): self
    {
        $new = clone $this;
        $new->pageSize = $pageSize;
        return $new;
    }
}
