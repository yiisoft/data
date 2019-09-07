<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader;

use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Data\Reader\Criterion\All;
use Yiisoft\Data\Reader\Criterion\Any;
use Yiisoft\Data\Reader\Criterion\CriteronInterface;
use Yiisoft\Data\Reader\Criterion\Equals;
use Yiisoft\Data\Reader\Criterion\GreaterThan;
use Yiisoft\Data\Reader\Criterion\GreaterThanOrEqual;
use Yiisoft\Data\Reader\Criterion\LessThan;
use Yiisoft\Data\Reader\Criterion\LessThanOrEqual;
use Yiisoft\Data\Reader\Criterion\In;
use Yiisoft\Data\Reader\Criterion\Like;
use Yiisoft\Data\Reader\Criterion\Not;

final class ArrayDataReader implements DataReaderInterface, SortableDataInterface, FilterableDataInterface, OffsetableDataInterface, CountableDataInterface
{
    private $data;
    private $sort;

    /**
     * @var CriteronInterface
     */
    private $filterCriteria;

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

    public function getSort(): ?Sort
    {
        return $this->sort;
    }

    /**
     * Sorts the data models according to the given sort definition.
     * @param array $items the models to be sorted
     * @param Sort $sort the sort definition
     * @return array the sorted data models
     */
    private function sortItems(array $items, Sort $sort): array
    {
        $criteria = $sort->getCriteria();
        if ($criteria !== []) {
            ArrayHelper::multisort($items, array_keys($criteria), array_values($criteria));
        }

        return $items;
    }

    private function filterItems(array $items): array
    {
        $filteredItems = [];
        foreach ($items as $item) {
            if ($this->matchFilter($item, $this->filterCriteria->toArray())) {
                $filteredItems[] = $item;
            }
        }
        return $filteredItems;
    }

    private function matchFilter(array $item, array $criterion): bool
    {
        $operation = array_shift($criterion);
        $arguments = $criterion;

        switch ($operation) {
            case Not::getOperator():
                return !$this->matchFilter($item, $arguments[0]);
            case Any::getOperator():
                foreach ($arguments[0] as $subCriterion) {
                    if ($this->matchFilter($item, $subCriterion)) {
                        return true;
                    }
                }
                return false;
            case All::getOperator():
                foreach ($arguments[0] as $subCriterion) {
                    if (!$this->matchFilter($item, $subCriterion)) {
                        return false;
                    }
                }
                return true;
            case Equals::getOperator():
                [$field, $value] = $arguments;
                return $item[$field] == $value;
            case GreaterThan::getOperator():
                [$field, $value] = $arguments;
                return $item[$field] > $value;
            case GreaterThanOrEqual::getOperator():
                [$field, $value] = $arguments;
                return $item[$field] >= $value;
            case LessThan::getOperator():
                [$field, $value] = $arguments;
                return $item[$field] < $value;
            case LessThanOrEqual::getOperator():
                [$field, $value] = $arguments;
                return $item[$field] <= $value;
            case In::getOperator():
                [$field, $values] = $arguments;
                return in_array($item[$field], $values, false);
            case Like::getOperator():
                [$field, $value] = $arguments;
                return stripos($item[$field], $value) !== false;
            default:
                throw new \RuntimeException("Operation \"$operation\" is not supported");
        }
    }

    public function withFilter(?CriteronInterface $criteria): self
    {
        $new = clone $this;
        $new->filterCriteria = $criteria;
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

        if ($this->filterCriteria !== null) {
            $data = $this->filterItems($data);
        }

        if ($this->sort !== null) {
            $data = $this->sortItems($data, $this->sort);
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
