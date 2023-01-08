<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\IterableFilterHandler;

use DateTimeInterface;

final class GreaterThanOrEqual extends Compare
{
    public function getOperator(): string
    {
        return \Yiisoft\Data\Reader\Filter\GreaterThanOrEqual::getOperator();
    }

    protected function compare(mixed $itemValue, mixed $argumentValue): bool
    {
        if (!$itemValue instanceof DateTimeInterface) {
            return $itemValue >= $argumentValue;
        }

        return $argumentValue instanceof DateTimeInterface
            && $itemValue->getTimestamp() >= $argumentValue->getTimestamp();
    }
}
