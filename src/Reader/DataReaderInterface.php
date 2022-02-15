<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader;

use IteratorAggregate;

/**
 * @template TKey as array-key
 * @template TValue
 *
 * @extends ReadableDataInterface<TKey, TValue>
 * @extends IteratorAggregate<TKey, TValue>
 */
interface DataReaderInterface extends
    ReadableDataInterface,
    OffsetableDataInterface,
    CountableDataInterface,
    SortableDataInterface,
    FilterableDataInterface,
    IteratorAggregate
{
}
