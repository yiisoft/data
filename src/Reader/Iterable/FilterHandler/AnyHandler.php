<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\FilterHandler;

use InvalidArgumentException;
use LogicException;
use Yiisoft\Data\Reader\Filter\Any;
use Yiisoft\Data\Reader\FilterInterface;
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

    public function match(object|array $item, FilterInterface $filter, array $iterableFilterHandlers): bool
    {
        if (!$filter instanceof Any) {
            throw new InvalidArgumentException('Incorrect filter.');
        }

        foreach ($filter->getFilters() as $subFilter) {
            $filterHandler = $iterableFilterHandlers[$subFilter::class] ?? null;
            if ($filterHandler === null) {
                throw new LogicException(sprintf('Filter "%s" is not supported.', $subFilter::class));
            }
            if ($filterHandler->match($item, $subFilter, $iterableFilterHandlers)) {
                return true;
            }
        }

        return false;
    }
}
