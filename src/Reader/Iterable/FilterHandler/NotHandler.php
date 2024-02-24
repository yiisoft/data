<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\FilterHandler;

use InvalidArgumentException;
use LogicException;
use Yiisoft\Data\Reader\Filter\Not;
use Yiisoft\Data\Reader\FilterInterface;
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

    public function match(array|object $item, FilterInterface $filter, array $iterableFilterHandlers): bool
    {
        if (!$filter instanceof Not) {
            throw new InvalidArgumentException('Incorrect filter.');
        }

        $filterHandler = $iterableFilterHandlers[$filter->filter::class] ?? null;
        if ($filterHandler === null) {
            throw new LogicException(sprintf('Filter "%s" is not supported.', $filter->filter::class));
        }
        return !$filterHandler->match($item, $filter->filter, $iterableFilterHandlers);
    }
}
