<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\Processor;

use function in_array;

class Any extends GroupProcessor
{
    public function getOperator(): string
    {
        return \Yiisoft\Data\Reader\Filter\Any::getOperator();
    }

    protected function checkResults(array $results): bool
    {
        return in_array(true, $results, true);
    }
}
