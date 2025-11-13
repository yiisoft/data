<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

use Yiisoft\Data\Reader\FilterInterface;

/**
 * `Not` filter negates another filter.
 */
final class Not implements FilterInterface
{
    /**
     * @param FilterInterface $filter Filter to negate.
     */
    public function __construct(
        public readonly FilterInterface $filter,
    ) {}
}
