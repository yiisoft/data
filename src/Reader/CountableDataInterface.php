<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader;

use Countable;

/**
 * Data that could be counted.
 */
interface CountableDataInterface extends Countable
{
    /**
     * @return non-negative-int Number of items in the data.
     */
    public function count(): int;
}
