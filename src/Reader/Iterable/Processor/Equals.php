<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\Processor;

use DateTimeInterface;

class Equals extends CompareProcessor
{
    public function getOperator(): string
    {
        return \Yiisoft\Data\Reader\Filter\Equals::getOperator();
    }

    protected function compare($itemValue, $argumentValue): bool
    {
        if (!$itemValue instanceof DateTimeInterface) {
            return $itemValue == $argumentValue;
        }

        return $argumentValue instanceof DateTimeInterface
            && $itemValue->getTimestamp() === $argumentValue->getTimestamp();
    }
}
