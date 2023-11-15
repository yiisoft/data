<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader;

/**
 * Readable data is a data set that could be read up to number of items
 * defined by limit either one by one or by getting an iterator.
 *
 * @template TKey as array-key
 * @template TValue as array|object
 */
interface ReadableDataInterface
{
    /**
     * Get iterable for the data set.
     *
     * @return iterable Iterable for the data.
     * @psalm-return iterable<TKey, TValue>
     */
    public function read(): iterable;
}
