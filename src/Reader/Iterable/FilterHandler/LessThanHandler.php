<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\FilterHandler;

use DateTimeInterface;
use Yiisoft\Data\Reader\Filter\LessThan;
use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\Iterable\Context;
use Yiisoft\Data\Reader\Iterable\IterableFilterHandlerInterface;

/**
 * `LessThan` iterable filter handler checks that the item's field value is less than the given value.
 */
final class LessThanHandler implements IterableFilterHandlerInterface
{
    public function getFilterClass(): string
    {
        return LessThan::class;
    }

    public function match(object|array $item, FilterInterface $filter, Context $context): bool
    {
        /** @var LessThan $filter */

        $itemValue = $context->readValue($item, $filter->field);
        $argumentValue = $filter->value;

        if (!$itemValue instanceof DateTimeInterface) {
            return $itemValue < $argumentValue;
        }

        return $argumentValue instanceof DateTimeInterface
            && $itemValue->getTimestamp() < $argumentValue->getTimestamp();
    }
}
