<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\FilterHandler;

use Yiisoft\Data\Reader\Filter\OrX;
use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\Iterable\Context;
use Yiisoft\Data\Reader\Iterable\IterableFilterHandlerInterface;

/**
 * `OrX` iterable filter handler allows combining multiple sub-filters.
 * The filter matches if any of the sub-filters match.
 */
final class OrXHandler implements IterableFilterHandlerInterface
{
    public function getFilterClass(): string
    {
        return OrX::class;
    }

    public function match(object|array $item, FilterInterface $filter, Context $context): bool
    {
        /** @var OrX $filter */

        foreach ($filter->filters as $subFilter) {
            $filterHandler = $context->getFilterHandler($subFilter::class);
            if ($filterHandler->match($item, $subFilter, $context)) {
                return true;
            }
        }

        return false;
    }
}
