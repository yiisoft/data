<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\FilterHandler;

use Yiisoft\Data\Reader\Filter\Equals;
use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\Iterable\Context;
use Yiisoft\Data\Reader\Iterable\IterableFilterHandlerInterface;

/**
 * `Equals` iterable filter handler checks that the item's field value matches the given value.
 */
final class EqualsHandler implements IterableFilterHandlerInterface
{
    public function getFilterClass(): string
    {
        return Equals::class;
    }

    public function match(object|array $item, FilterInterface $filter, Context $context): bool
    {
        /** @var Equals $filter */

        $itemValue = $context->readValue($item, $filter->field);
        $argumentValue = $filter->value;

        return $itemValue == $argumentValue;
    }
}
