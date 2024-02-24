<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\FilterHandler;

use DateTimeInterface;
use InvalidArgumentException;
use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Data\Reader\Filter\Between;
use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\Iterable\IterableFilterHandlerInterface;

/**
 * `Between` iterable filter handler checks that the item's field value
 * is between minimal and maximal values.
 */
final class BetweenHandler implements IterableFilterHandlerInterface
{
    public function getFilterClass(): string
    {
        return Between::class;
    }

    public function match(array|object $item, FilterInterface $filter, array $iterableFilterHandlers): bool
    {
        /** @var Between $filter */

        $value = ArrayHelper::getValue($item, $filter->field);

        if (!$value instanceof DateTimeInterface) {
            return $value >= $filter->minValue && $value <= $filter->maxValue;
        }

        return $filter->minValue instanceof DateTimeInterface
            && $filter->maxValue instanceof DateTimeInterface
            && $value->getTimestamp() >= $filter->minValue->getTimestamp()
            && $value->getTimestamp() <= $filter->maxValue->getTimestamp();
    }
}
