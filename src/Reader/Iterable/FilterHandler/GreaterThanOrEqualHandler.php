<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\FilterHandler;

use DateTimeInterface;
use Yiisoft\Data\Reader\Filter\GreaterThanOrEqual;
use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\Iterable\Context;
use Yiisoft\Data\Reader\Iterable\IterableFilterHandlerInterface;

/**
 * `GreaterThanOrEqual` iterable filter handler checks that the item's field value
 * is greater than or equal to the given value.
 */
final class GreaterThanOrEqualHandler implements IterableFilterHandlerInterface
{
    public function getFilterClass(): string
    {
        return GreaterThanOrEqual::class;
    }

    public function match(object|array $item, FilterInterface $filter, Context $context): bool
    {
        /** @var GreaterThanOrEqual $filter */

        $itemValue = $context->readValue($item, $filter->field);
        $argumentValue = $filter->value;

        if (!$itemValue instanceof DateTimeInterface) {
            return $itemValue >= $argumentValue;
        }

        return $argumentValue instanceof DateTimeInterface
            && $itemValue->getTimestamp() >= $argumentValue->getTimestamp();
    }
}
