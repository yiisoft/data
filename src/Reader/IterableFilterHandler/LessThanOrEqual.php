<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\IterableFilterHandler;

use DateTimeInterface;

/**
 * LessThanOrEqual iterable filter handler checks that the item's field value
 * is less than or equal to the given value.
 */
final class LessThanOrEqual extends Compare
{
    public function getOperator(): string
    {
        return \Yiisoft\Data\Reader\Filter\LessThanOrEqual::getOperator();
    }

    protected function compare(mixed $itemValue, mixed $argumentValue): bool
    {
        if (!$itemValue instanceof DateTimeInterface) {
            return $itemValue <= $argumentValue;
        }

        return $argumentValue instanceof DateTimeInterface
            && $itemValue->getTimestamp() <= $argumentValue->getTimestamp();
    }
}
