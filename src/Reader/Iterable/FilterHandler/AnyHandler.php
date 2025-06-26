<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\FilterHandler;

use Yiisoft\Data\Reader\Filter\Any;
use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\Iterable\Context;
use Yiisoft\Data\Reader\Iterable\IterableFilterHandlerInterface;

/**
 * `Any` iterable filter handler allows combining multiple sub-filters.
 * The filter matches if any of the sub-filters match.
 */
final class AnyHandler implements IterableFilterHandlerInterface
{
    public function getFilterClass(): string
    {
        return Any::class;
    }

    public function match(object|array $item, FilterInterface $filter, Context $context): bool
    {
        /** @var Any $filter */

        foreach ($filter->getFilters() as $subFilter) {
            $filterHandler = $context->getFilterHandler($subFilter::class);
            if ($filterHandler->match($item, $subFilter, $context)) {
                return true;
            }
        }

        return false;
    }
}
