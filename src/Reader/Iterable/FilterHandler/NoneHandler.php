<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\FilterHandler;

use Yiisoft\Data\Reader\Filter\None;
use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\Iterable\Context;
use Yiisoft\Data\Reader\Iterable\IterableFilterHandlerInterface;

/**
 * Handles the {@see None} filter for iterable data readers.
 * Never matches any items, effectively excluding all data.
 */
final class NoneHandler implements IterableFilterHandlerInterface
{
    public function getFilterClass(): string
    {
        return None::class;
    }

    public function match(object|array $item, FilterInterface $filter, Context $context): bool
    {
        return false;
    }
}
