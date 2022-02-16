<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\Processor;

use function in_array;

class All extends GroupProcessor
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
