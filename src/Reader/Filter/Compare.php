<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

use DateTimeInterface;
use Yiisoft\Data\Reader\FilterInterface;

/**
 * `Compare` filter is a base class that defines a criteria for comparing field value with a given value.
 * The operator is defined by child classes.
 */
abstract class Compare implements FilterInterface
{
    private bool|DateTimeInterface|float|int|string $value;

    /**
     * @param string $field Name of the field to compare.
     * @param bool|DateTimeInterface|float|int|string $value Value to compare to.
     */
    public function __construct(
        public readonly string $field,
        bool|DateTimeInterface|float|int|string $value
    ) {
        $this->value = $value;
    }

    public function getValue(): float|DateTimeInterface|bool|int|string
    {
        return $this->value;
    }

    /**
     * @param bool|DateTimeInterface|float|int|string $value Value to compare to.
     */
    final public function withValue(bool|DateTimeInterface|float|int|string $value): static
    {
        $new = clone $this;
        $new->value = $value;
        return $new;
    }
}
