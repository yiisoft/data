<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

use Stringable;
use Yiisoft\Data\Reader\FilterInterface;

/**
 * `Like` filter defines a criteria for ensuring field value is like-match to a given value.
 */
final class Like implements FilterInterface
{
    /**
     * @param string $field Name of the field to compare.
     * @param string|Stringable $value Value to like-compare with.
     * @param bool|null $caseSensitive Whether search must be case-sensitive:
     *
     * - `null` - depends on implementation;
     * - `true` - case-sensitive;
     * - `false` - case-insensitive.
     * @param LikeMode $mode Matching mode.
     */
    public function __construct(
        public readonly string $field,
        public readonly string|Stringable $value,
        public readonly ?bool $caseSensitive = null,
        public readonly LikeMode $mode = LikeMode::Contains,
    ) {}
}
