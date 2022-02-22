<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\Processor;

class LessThanOrEqual extends CompareProcessor
{
    public function getOperator(): string
    {
        return \Yiisoft\Data\Reader\Filter\LessThanOrEqual::getOperator();
    }

    protected function compare($itemValue, $argumentValue): bool
    {
        return $itemValue <= $argumentValue;
    }
}
