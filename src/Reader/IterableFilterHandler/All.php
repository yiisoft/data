<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\IterableFilterHandler;

use function in_array;

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
