<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\FilterHandler;

use LogicException;
use Yiisoft\Data\Reader\Filter\Not;
use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\Iterable\Context;
use Yiisoft\Data\Reader\Iterable\IterableFilterHandlerInterface;

use function sprintf;

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

        $subFilter = $filter->getFilter();

        $filterHandler = $context->tryFindFilterHandler($subFilter::class);
        if ($filterHandler === null) {
            throw new LogicException(sprintf('Filter "%s" is not supported.', $subFilter::class));
        }
        return !$filterHandler->match($item, $subFilter, $context);
    }
}
