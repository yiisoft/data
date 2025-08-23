<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\FilterHandler;

use Yiisoft\Data\Reader\Filter\GreaterThan;
use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\Iterable\Context;
use Yiisoft\Data\Reader\Iterable\IterableFilterHandlerInterface;

/**
 * `GreaterThan` iterable filter handler checks that the item's field value is greater than the given value.
 */
final class GreaterThanHandler implements IterableFilterHandlerInterface
{
    public function getFilterClass(): string
    {
        return GreaterThan::class;
    }

    public function match(object|array $item, FilterInterface $filter, Context $context): bool
    {
        /** @var GreaterThan $filter */

        $itemValue = $context->readValue($item, $filter->field);
        $argumentValue = $filter->value;

        return $itemValue > $argumentValue;
    }
}
