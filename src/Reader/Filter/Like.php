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
     * @param bool|null $caseSensitive Whether search must be case-sensitive:
     *
     * - `null` - depends on implementation;
     * - `true` - case-sensitive;
     * - `false` - case-insensitive.
     */
    public function __construct(
        private readonly string $field,
        private readonly string $value,
        private readonly ?bool $caseSensitive = null,
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

    public function isCaseSensitive(): ?bool
    {
        return $this->caseSensitive;
    }
}
