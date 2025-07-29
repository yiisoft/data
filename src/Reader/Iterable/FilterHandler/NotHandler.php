<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\FilterHandler;

use Yiisoft\Data\Reader\Filter\Not;
use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\Iterable\Context;
use Yiisoft\Data\Reader\Iterable\IterableFilterHandlerInterface;

/**
 * `Not` iterable filter handler negates another filter.
 */
final class NotHandler implements IterableFilterHandlerInterface
{
    public function getFilterClass(): string
    {
        return Not::class;
    }

    public function match(object|array $item, FilterInterface $filter, Context $context): bool
    {
        /** @var Not $filter */

        $subFilter = $filter->filter;

        $filterHandler = $context->getFilterHandler($subFilter::class);
        return !$filterHandler->match($item, $subFilter, $context);
    }
}
