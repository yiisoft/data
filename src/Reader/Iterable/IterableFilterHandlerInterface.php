<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable;

use Yiisoft\Data\Reader\FilterHandlerInterface;
use Yiisoft\Data\Reader\FilterInterface;

/**
 * Iterable filter handler checks whether an item matches criteria defined
 * in the filter with the same operator.
 */
interface IterableFilterHandlerInterface extends FilterHandlerInterface
{
    /**
     * Check whether an item matches iterable filter handlers
     * for the filters with matching operator active.
     *
     * @param array|object $item Item to check.
     * @param FilterInterface $filter Matched filter.
     * @param IterableFilterHandlerInterface[] $iterableFilterHandlers Iterable filter handlers to use in case it is
     * a group filter.
     *
     * @return bool Whether item matches the filter.
     */
    public function match(array|object $item, FilterInterface $filter, array $iterableFilterHandlers): bool;
}
