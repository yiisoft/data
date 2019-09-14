<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader;

use Traversable;
use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Data\Reader\Filter\FilterInterface;
use Yiisoft\Data\Reader\Filter\Unit\FilterUnitInterface;
use Yiisoft\Data\Reader\Filter\Unit\VariableUnit\VariableUnitInterface;

class IterableDataReader implements DataReaderInterface, SortableDataInterface, FilterableDataInterface, OffsetableDataInterface, CountableDataInterface
{
    protected $data;
    private $sort;

    /**
     * @var FilterInterface
     */
    private $filter;

    private $limit = self::DEFAULT_LIMIT;
    private $offset = 0;
    private $filterUnits = [];

    public function __construct(iterable $data)
    {
        $this->data = $data;
        $this->filterUnits = $this->withFilterUnits(
            new Filter\Unit\VariableUnit\All(),
            new Filter\Unit\VariableUnit\Any(),
            new Filter\Unit\VariableUnit\Equals(),
            new Filter\Unit\VariableUnit\GreaterThan(),
            new Filter\Unit\VariableUnit\GreaterThanOrEqual(),
            new Filter\Unit\VariableUnit\In(),
            new Filter\Unit\VariableUnit\LessThan(),
            new Filter\Unit\VariableUnit\LessThanOrEqual(),
            new Filter\Unit\VariableUnit\Like(),
            new Filter\Unit\VariableUnit\Not()
        )->filterUnits;
    }

    public function withSort(?Sort $sort): self
    {
        $new = clone $this;
        $new->sort = $sort;
        return $new;
    }

    public function getSort(): ?Sort
    {
        return $this->sort;
    }

    /**
     * Sorts data items according to the given sort definition.
     * @param iterable $items the items to be sorted
     * @param Sort $sort the sort definition
     * @return array the sorted items
     */
    private function sortItems(iterable $items, Sort $sort): iterable
    {
        $criteria = $sort->getCriteria();
        if ($criteria !== []) {
            $items = $this->iterableToArray($items);
            ArrayHelper::multisort($items, array_keys($criteria), array_values($criteria));
        }

        return $items;
    }

    protected function matchFilter(array $item, array $filter): bool
    {
        $operation = array_shift($filter);
        $arguments = $filter;

        $unit = $this->filterUnits[$operation] ?? null;
        if($unit === null) {
            throw new \RuntimeException(sprintf('Operation "%s" is not supported', $operation));
        }
        /* @var $unit \Yiisoft\Data\Reader\Filter\Unit\VariableUnit\VariableUnitInterface */
        return $unit->match($item, $arguments, $this->filterUnits);
    }

    public function withFilter(?FilterInterface $filter): self
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
        $filter = null;
        if ($this->filter !== null) {
            $filter = $this->filter->toArray();
        }

        $data = [];
        $skipped = 0;

        $sortedData = $this->sort === null
            ? $this->data
            : $this->sortItems($this->data, $this->sort);

        foreach ($sortedData as $item) {
            // do not return more than limit items
            if (count($data) === $this->limit) {
                break;
            }

            // skip offset items
            if ($skipped < $this->offset) {
                $skipped++;
                continue;
            }

            // filter items
            if ($filter === null || $this->matchFilter($item, $filter)) {
                $data[] = $item;
            }
        }

        return $data;
    }

    public function withOffset(int $offset): self
    {
        $new = clone $this;
        $new->offset = $offset;
        return $new;
    }

    public function count(): int
    {
        return count($this->read());
    }

    private function iterableToArray(iterable $iterable): array
    {
        return $iterable instanceof Traversable ? iterator_to_array($iterable, true) : (array)$iterable;
    }

    public function withFilterUnits(FilterUnitInterface... $filterUnits): self
    {
        $new = clone $this;
        $units = [];
        foreach($filterUnits as $key => $filterUnit) {
            if($filterUnit instanceof VariableUnitInterface) {
                $units[$filterUnit->getOperator()] = $filterUnit;
            }
        }
        $new->filterUnits = array_merge($this->filterUnits, $units);
        return $new;
    }
}
