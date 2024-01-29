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
     * @return int Number of items in the data.
     *
     * @psalm-return non-negative-int
     */
    public function count(): int;
}
