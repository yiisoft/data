<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\IterableFilterHandler;

use function in_array;

/**
 * All iterable filter handler allows combining multiple sub-filters.
 * The filter matches only if all the sub-filters match.
 */
final class All extends Group
{
    public function getOperator(): string
    {
        return \Yiisoft\Data\Reader\Filter\All::getOperator();
    }

    protected function checkResults(array $results): bool
    {
        return !in_array(false, $results, true);
    }
}
