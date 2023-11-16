<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader;

/**
 * Readable data is a data set that could be read up by getting an iterator or reading one item from set.
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

    /**
     * Get the first item from the data set.
     * Note that invoking this method does not impact the data set or its pointer.
     *
     * @return array|object|null An item or null if there is none.
     * @psalm-return TValue|null
     */
    public function readOne(): array|object|null;
}
