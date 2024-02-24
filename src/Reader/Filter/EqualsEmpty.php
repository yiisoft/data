<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

use Yiisoft\Data\Reader\FilterInterface;

/**
 * `EqualsEmpty` filter defines a criteria for ensuring field value is empty.
 */
final class EqualsEmpty implements FilterInterface
{
    /**
     * @param string $field Name of the field to check.
     */
    public function __construct(
        public readonly string $field
    ) {
    }
}
