<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

use DateTimeInterface;
use Yiisoft\Data\Reader\FilterInterface;

/**
 * `Between` filter defines a criteria, so the value of the field with a given name
 * is between the minimal value and the maximal value.
 */
final class Between implements FilterInterface
{
    /**
     * @param string $field Name of the field to compare.
     * @param bool|DateTimeInterface|float|int|string $minValue Minimal field value.
     * @param bool|DateTimeInterface|float|int|string $maxValue Maximal field value.
     */
    public function __construct(
        private readonly string $field,
        private readonly bool|DateTimeInterface|float|int|string $minValue,
        private readonly bool|DateTimeInterface|float|int|string $maxValue
    ) {
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getMinValue(): float|DateTimeInterface|bool|int|string
    {
        return $this->minValue;
    }

    public function getMaxValue(): float|DateTimeInterface|bool|int|string
    {
        return $this->maxValue;
    }
}
