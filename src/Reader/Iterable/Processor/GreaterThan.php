<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\Processor;

class GreaterThan extends CompareProcessor
{
    public function getOperator(): string
    {
        return \Yiisoft\Data\Reader\Filter\GreaterThan::getOperator();
    }

    protected function compare($itemValue, $argumentValue): bool
    {
        return $itemValue > $argumentValue;
    }
}
