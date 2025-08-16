<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\FilterHandler;

use Yiisoft\Data\Reader\Filter\All;
use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\Iterable\Context;
use Yiisoft\Data\Reader\Iterable\IterableFilterHandlerInterface;

/**
 * Handles the {@see All} filter for iterable data readers.
 * Always matches all items, effectively disabling filtering.
 */
final class AllHandler implements IterableFilterHandlerInterface
{
    public function getFilterClass(): string
    {
        return All::class;
    }

    public function match(object|array $item, FilterInterface $filter, Context $context): bool
    {
        return true;
    }
}
