<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

use DateTimeInterface;
use Stringable;
use Yiisoft\Data\Reader\FilterInterface;

/**
 * `Between` filter defines a criteria, so the value of the field with a given name
 * is between the minimal value and the maximal value.
 */
final class Between implements FilterInterface
{
    /**
     * @param string $field Name of the field to compare.
     * @param bool|DateTimeInterface|float|int|string|Stringable $minValue Minimal field value.
     * @param bool|DateTimeInterface|float|int|string|Stringable $maxValue Maximal field value.
     */
    public function __construct(
        public readonly string $field,
        public readonly bool|DateTimeInterface|float|int|string|Stringable $minValue,
        public readonly bool|DateTimeInterface|float|int|string|Stringable $maxValue,
    ) {}
}
