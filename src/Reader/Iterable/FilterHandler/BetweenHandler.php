<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\FilterHandler;

use Yiisoft\Data\Reader\Filter\Between;
use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\Iterable\Context;
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

    public function match(object|array $item, FilterInterface $filter, Context $context): bool
    {
        /** @var Between $filter */

        $value = $context->readValue($item, $filter->field);
        $min = $filter->minValue;
        $max = $filter->maxValue;

        return $value >= $min && $value <= $max;
    }
}
