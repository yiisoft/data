<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\FilterHandler;

use DateTimeInterface;
use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Data\Reader\Filter\LessThanOrEqual;
use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\Iterable\IterableFilterHandlerInterface;

/**
 * `LessThanOrEqual` iterable filter handler checks that the item's field value
 * is less than or equal to the given value.
 */
final class LessThanOrEqualHandler implements IterableFilterHandlerInterface
{
    public function getFilterClass(): string
    {
        return LessThanOrEqual::class;
    }

    public function match(object|array $item, FilterInterface $filter, array $iterableFilterHandlers): bool
    {
        /** @var LessThanOrEqual $filter */

        $itemValue = ArrayHelper::getValue($item, $filter->getField());
        $argumentValue = $filter->getValue();

        if (!$itemValue instanceof DateTimeInterface) {
            return $itemValue <= $argumentValue;
        }

        return $argumentValue instanceof DateTimeInterface
            && $itemValue->getTimestamp() <= $argumentValue->getTimestamp();
    }
}
