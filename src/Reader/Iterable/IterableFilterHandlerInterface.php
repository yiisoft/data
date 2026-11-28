<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable;

use Yiisoft\Data\Reader\FilterInterface;

/**
 * Iterable filter handler checks whether an item matches criteria defined
 * in the filter with the same operator.
 *
 * @template TFilter of FilterInterface = FilterInterface
 */
interface IterableFilterHandlerInterface
{
    /**
     * Check whether an item matches iterable filter handlers
     * for the filters with matching operator active.
     *
     * @param array<mixed>|object $item Item to check.
     * @param TFilter $filter Matched filter.
     *
     * @return bool Whether item matches the filter.
     */
    public function match(array|object $item, FilterInterface $filter, Context $context): bool;

    /**
     * Get matching filter class name.
     *
     * If the filter is active, a corresponding handler will be used during matching.
     *
     * @return class-string<TFilter> The filter class name.
     */
    public function getFilterClass(): string;
}
