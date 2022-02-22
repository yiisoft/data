<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\Processor;

use function in_array;
use function is_array;

class In extends CompareProcessor
{
    public function getOperator(): string
    {
        return \Yiisoft\Data\Reader\Filter\In::getOperator();
    }

    protected function compare($itemValue, $argumentValue): bool
    {
        return is_array($argumentValue) && in_array($itemValue, $argumentValue, false);
    }
}
