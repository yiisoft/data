<?php

namespace Yiisoft\Data\Reader;

use Yiisoft\Arrays\ArrayHelper;

class ArrayDataReader implements DataReaderInterface, SortableDataInterface, FilterableDataInterface, OffsetableDataInterface, CountableDataInterface
{
    private $data;
    private $sort;
    private $filter;

    private $limit = self::DEFAULT_LIMIT;
    private $offset = 0;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function withSort(?Sort $sort): self
    {
        $new = clone $this;
        $new->sort = $sort;
        return $new;
    }

    /**
     * Sorts the data models according to the given sort definition.
     * @param array $items the models to be sorted
     * @param Sort $sort the sort definition
     * @return array the sorted data models
     */
    protected function sortItems(array $items, Sort $sort): array
    {
        $criteria = $sort->getCriteria();
        if ($criteria !== []) {
            ArrayHelper::multisort($items, array_keys($criteria), array_values($criteria));
        }

        return $items;
    }

    protected function filterItems(array $items, Filter $filter): array
    {
        $criteria = $filter->getCriteria();
        if ($criteria !== []) {
            // TODO: implement
        }

        return $items;
    }

    public function withFilter(?Filter $filter): self
    {
        $new = clone $this;
        $new->filter = $filter;
        return $new;
    }

    public function withLimit(int $limit): self
    {
        $new = clone $this;
        $new->limit = $limit;
        return $new;
    }

    public function read(): iterable
    {
        $data = $this->data;

        if ($this->filter !== null) {
            $data = $this->filterItems($this->data, $this->filter);
        }

        if ($this->sort !== null) {
            $data = $this->sortItems($this->data, $this->sort);
        }
        return array_slice($data, $this->offset, $this->limit);
    }

    public function withOffset(int $offset): self
    {
        $new = clone $this;
        $new->offset = $offset;
        return $new;
    }

    public function count(): int
    {
        return count($this->data);
    }
}
