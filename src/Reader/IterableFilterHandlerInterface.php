<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader;

/**
 * Iterable filter handler checks whether an item matches criteria.
 *
 * post-processes data items by iterating over them and checking whether
 * each item match.
 */
interface IterableFilterHandlerInterface
{
    /**
     * Get matching filter operator.
     *
     * If the filter with such operator is active, a corresponding
     * iterable filter handler will be used during matching.
     *
     * @return string Operator.
     */
    public function getOperator(): string;

    /**
     * Check whether an item matches iterable filter handlers
     * for the filters with matching operator active.
     *
     * @param array|object $item Item to check.
     * @param array $arguments Arguments to pass to iterable filter handlers.
     * @param array $iterableFilterHandlers Iterable filter handlers to use.
     * @return bool Whether item matches the filter.
     */
    public function match(array|object $item, array $arguments, array $iterableFilterHandlers): bool;
}
