<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\Processor;

use function is_string;
use function stripos;

class Like extends CompareProcessor
{
    public function getOperator(): string
    {
        return \Yiisoft\Data\Reader\Filter\Like::getOperator();
    }

    protected function compare($itemValue, $argumentValue): bool
    {
        return is_string($itemValue) && is_string($argumentValue) && stripos($itemValue, $argumentValue) !== false;
    }
}
