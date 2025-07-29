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
     * @param LikeMode $mode Matching mode:
     *
     * - `LikeMode::CONTAINS` - field value contains the search value (default);
     * - `LikeMode::STARTS_WITH` - field value starts with the search value;
     * - `LikeMode::ENDS_WITH` - field value ends with the search value.
     */
    public function __construct(
        public readonly string $field,
        public readonly string $value,
        public readonly ?bool $caseSensitive = null,
        public readonly LikeMode $mode = LikeMode::CONTAINS,
    ) {
    }
}
