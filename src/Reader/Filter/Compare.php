<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

use DateTimeInterface;
use Stringable;
use Yiisoft\Data\Reader\FilterInterface;

/**
 * `Compare` filter is a base class that defines a criteria for comparing field value with a given value.
 */
abstract class Compare implements FilterInterface
{
    /**
     * @param string $field Name of the field to compare.
     * @param bool|DateTimeInterface|float|int|string|Stringable $value Value to compare to.
     */
    final public function __construct(
        public readonly string $field,
        public readonly bool|DateTimeInterface|float|int|string|Stringable $value,
    ) {
    }

    /**
     * @param bool|DateTimeInterface|float|int|string|Stringable $value Value to compare to.
     */
    final public function withValue(bool|DateTimeInterface|float|int|string|Stringable $value): static
    {
        return new static($this->field, $value);
    }
}
