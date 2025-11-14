<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

use Yiisoft\Data\Reader\FilterInterface;

/**
 * `EqualsNull` filter defines a criteria for ensuring field value is null.
 */
final class EqualsNull implements FilterInterface
{
    /**
     * @param string $field Name of the field to check.
     */
    public function __construct(
        public readonly string $field,
    ) {}
}
