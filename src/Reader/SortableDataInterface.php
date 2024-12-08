<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader;

/**
 * A data set that could be sorted.
 * The sorting set may be used by a data reader that supports sorting.
 *
 * @template TKey as array-key
 * @template TValue as array|object
 *
 * @extends ReadableDataInterface<TKey, TValue>
 */
interface SortableDataInterface extends ReadableDataInterface
{
    /**
     * Get a new instance with a sorting set.
     *
     * @param Sort|null $sort Sorting criteria or null for no sorting.
     *
     * @return static New instance.
     * @psalm-return $this
     */
    public function withSort(?Sort $sort): static;

    /**
     * Get a current sorting criteria.
     *
     * @return Sort|null Current sorting criteria or null for no sorting.
     */
    public function getSort(): ?Sort;
}
