<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

use Yiisoft\Data\Reader\FilterInterface;

/**
 * `AndX` filter allows combining multiple sub-filters using "AND" operator.
 *
 * ```php
 * $dataReader->withFilter(
 *   new AndX(
 *     new GreaterThan('id', 88),
 *     new Equals('state', 2),
 *     new Like('name', 'eva'),
 *   )
 * );
 * ```
 */
final class AndX implements FilterInterface
{
    /**
     * @var FilterInterface[] Sub-filters to use.
     */
    public readonly array $filters;

    /**
     * @param FilterInterface ...$filters Sub-filters to use.
     */
    public function __construct(FilterInterface ...$filters)
    {
        $this->filters = $filters;
    }
}
