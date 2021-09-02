<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

use InvalidArgumentException;

use function is_array;
use function is_string;

abstract class GroupFilter implements FilterInterface
{
    /**
     * @var FilterInterface[]|array[]
     */
    private array $filters;

    public function __construct(FilterInterface ...$filters)
    {
        $this->filters = $filters;
    }

    public function toArray(): array
    {
        $filtersArray = [];
        foreach ($this->filters as $filter) {
            if ($filter instanceof FilterInterface) {
                $filter = $filter->toArray();
            }
            $filtersArray[] = $filter;
        }
        return [static::getOperator(), $filtersArray];
    }

    /**
     * Building criteria with array
     *
     * ~~~
     * $dataReader->withFilter((new All())->withFiltersArray(
     *   [
     *     ['>', 'id', 88],
     *     ['or', [
     *        ['=', 'state', 2],
     *        ['like', 'name', 'eva'],
     *     ],
     *   ]
     * ))
     * ~~~
     *
     * @param array $filtersArray
     *
     * @return static
     */
    public function withFiltersArray(array $filtersArray): self
    {
        foreach ($filtersArray as $key => $filter) {
            if ($filter instanceof FilterInterface) {
                continue;
            }

            if (!is_array($filter)) {
                throw new InvalidArgumentException(sprintf('Invalid filter at "%s" key', $key));
            }
            $first = array_shift($filter);
            if (!is_string($first) || $first === '') {
                throw new InvalidArgumentException(sprintf('Invalid filter operator on "%s" key', $key));
            }
        }
        $new = clone $this;
        $new->filters = $filtersArray;
        return $new;
    }
}
