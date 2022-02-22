<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

use InvalidArgumentException;

use function array_shift;
use function is_array;
use function is_string;
use function sprintf;

abstract class GroupFilter implements FilterInterface
{
    /**
     * @var array[]|FilterInterface[]
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
     * Building criteria with array.
     *
     * ```php
     * $dataReader->withFilter((new All())->withFiltersArray(
     *   [
     *     ['>', 'id', 88],
     *     ['or', [
     *        ['=', 'state', 2],
     *        ['like', 'name', 'eva'],
     *     ],
     *   ]
     * ));
     * ```
     *
     * @param array[]|FilterInterface[] $filtersArray
     *
     * @return static
     */
    public function withFiltersArray(array $filtersArray): self
    {
        foreach ($filtersArray as $key => $filter) {
            if ($filter instanceof FilterInterface) {
                continue;
            }

            /** @psalm-suppress DocblockTypeContradiction */
            if (!is_array($filter)) {
                throw new InvalidArgumentException(sprintf('Invalid filter on "%s" key.', $key));
            }

            $operator = array_shift($filter);

            if (!is_string($operator) || $operator === '') {
                throw new InvalidArgumentException(sprintf('Invalid filter operator on "%s" key.', $key));
            }
        }

        $new = clone $this;
        $new->filters = $filtersArray;
        return $new;
    }
}
