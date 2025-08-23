<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\FilterHandler;

use DateTimeInterface;
use Yiisoft\Data\Reader\Filter\LessThanOrEqual;
use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\Iterable\Context;
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

    public function match(object|array $item, FilterInterface $filter, Context $context): bool
    {
        /** @var LessThanOrEqual $filter */

        $itemValue = $context->readValue($item, $filter->field);
        $argumentValue = $filter->value;

        if ($itemValue === null) {
            return false;
        }

        return $itemValue <= $argumentValue;
    }
}
