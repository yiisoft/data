<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader;

use IteratorAggregate;

/**
 * @template TKey as array-key
 * @template TValue as array|object
 *
 * @extends ReadableDataInterface<TKey, TValue>
 * @extends IteratorAggregate<TKey, TValue>
 */
interface DataReaderReaderInterface extends
    ReadableDataInterface,
    OffsetableDataInterface,
    CountableDataInterface,
    SortableDataInterface,
    FilterableDataReaderInterface,
    IteratorAggregate
{
}
