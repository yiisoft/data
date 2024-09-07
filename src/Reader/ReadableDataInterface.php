<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader;

/**
 * Readable data is a data set that could be read up by getting an iterator or reading one item from a set.
 *
 * @template TKey as array-key
 * @template TValue as array|object
 */
interface ReadableDataInterface
{
    /**
     * Get iterable for the data set.
     *
     * @return iterable Iterable for the data. Note that keys could be anything so you shouldn't rely on these.
     * @psalm-return iterable<TKey, TValue>
     */
    public function read(): iterable;

    /**
     * Get one item from the data set. Which item is returned is up to implementation.
     * Note that invoking this method doesn't impact the data set or its pointer.
     *
     * @return array|object|null An item or null if there is none.
     * @psalm-return TValue|null
     */
    public function readOne(): array|object|null;
}
