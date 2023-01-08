<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\Handler;

use function in_array;

final class Any extends Group
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
