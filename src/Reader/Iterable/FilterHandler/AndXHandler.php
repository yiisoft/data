<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\FilterHandler;

use Yiisoft\Data\Reader\Filter\AndX;
use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\Iterable\Context;
use Yiisoft\Data\Reader\Iterable\IterableFilterHandlerInterface;

/**
 * `AndX` iterable filter handler allows combining multiple sub-filters.
 * The filter matches only if all the sub-filters match.
 */
final class AndXHandler implements IterableFilterHandlerInterface
{
    public function getFilterClass(): string
    {
        return AndX::class;
    }

    public function match(object|array $item, FilterInterface $filter, Context $context): bool
    {
        /** @var AndX $filter */

        foreach ($filter->filters as $subFilter) {
            $filterHandler = $context->getFilterHandler($subFilter::class);
            if (!$filterHandler->match($item, $subFilter, $context)) {
                return false;
            }
        }

        return true;
    }
}
