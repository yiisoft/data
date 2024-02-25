<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

use Yiisoft\Data\Reader\FilterInterface;

/**
 * `Group` filter is an abstract class that allows combining multiple sub-filters.
 */
abstract class Group implements FilterInterface
{
    /**
     * @var FilterInterface[] Sub-filters to use.
     */
    private array $filters;

    /**
     * @param FilterInterface ...$filters Sub-filters to use.
     */
    public function __construct(FilterInterface ...$filters)
    {
        $this->filters = $filters;
    }

    /**
     * @return FilterInterface[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }
}
