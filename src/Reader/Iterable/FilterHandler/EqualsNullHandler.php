<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\FilterHandler;

use Yiisoft\Data\Reader\Filter\EqualsNull;
use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\Iterable\Context;
use Yiisoft\Data\Reader\Iterable\IterableFilterHandlerInterface;

/**
 * `EqualsNull` iterable filter handler checks that the item's field value is null.
 */
final class EqualsNullHandler implements IterableFilterHandlerInterface
{
    public function getFilterClass(): string
    {
        return EqualsNull::class;
    }

    public function match(object|array $item, FilterInterface $filter, Context $context): bool
    {
        /** @var EqualsNull $filter */

        return $context->readValue($item, $filter->field) === null;
    }
}
