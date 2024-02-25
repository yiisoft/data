<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

use Yiisoft\Data\Reader\FilterInterface;

/**
 * `Like` filter defines a criteria for ensuring field value is like-match to a given value.
 */
final class Like implements FilterInterface
{
    /**
     * @param string $field Name of the field to compare.
     * @param string $value Value to like-compare with.
     */
    public function __construct(
        private readonly string $field,
        private readonly string $value,
    ) {
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
