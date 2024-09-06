<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader;

/**
 * Filter handler checks whether an item matches filter.
 */
interface FilterHandlerInterface
{
    /**
     * Get matching filter class name.
     *
     * If the filter is active, a corresponding handler will be used during matching.
     *
     * @return string The filter class name.
     *
     * @psalm-return class-string
     */
    public function getFilterClass(): string;
}
