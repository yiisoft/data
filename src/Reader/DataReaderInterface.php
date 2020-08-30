<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader;

use IteratorAggregate;

interface DataReaderInterface extends
    ReadableDataInterface,
    OffsetableDataInterface,
    CountableDataInterface,
    SortableDataInterface,
    FilterableDataInterface,
    IteratorAggregate
{
}
