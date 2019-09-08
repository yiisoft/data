<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

abstract class GroupFilter implements FilterInterface
{
    /**
     * @var FilterInterface[]
     */
    private $filters;

    public function __construct(FilterInterface...$filters)
    {
        $this->filters = $filters;
    }

    public function toArray(): array
    {
        $filtersArray = [];
        foreach ($this->filters as $filter) {
            $filtersArray[] = $filter->toArray();
        }
        return [static::getOperator(), $filtersArray];
    }
}
