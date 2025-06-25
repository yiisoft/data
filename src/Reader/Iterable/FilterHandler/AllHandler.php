<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\FilterHandler;

use LogicException;
use Yiisoft\Data\Reader\Filter\All;
use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\Iterable\Context;
use Yiisoft\Data\Reader\Iterable\IterableFilterHandlerInterface;

use function sprintf;

/**
 * `All` iterable filter handler allows combining multiple sub-filters.
 * The filter matches only if all the sub-filters match.
 */
final class AllHandler implements IterableFilterHandlerInterface
{
    public function getFilterClass(): string
    {
        return All::class;
    }

    public function match(object|array $item, FilterInterface $filter, Context $context): bool
    {
        /** @var All $filter */

        foreach ($filter->getFilters() as $subFilter) {
            $filterHandler = $context->tryFindFilterHandler($subFilter::class);
            if ($filterHandler === null) {
                throw new LogicException(
                    sprintf('Filter "%s" is not supported.', $subFilter::class),
                );
            }
            if (!$filterHandler->match($item, $subFilter, $context)) {
                return false;
            }
        }

        return true;
    }
}
