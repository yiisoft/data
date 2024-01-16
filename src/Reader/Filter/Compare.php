<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

use DateTimeInterface;
use Yiisoft\Data\Reader\FilterAssert;
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
    public function __construct(private string $field, mixed $value)
    {
        $this->setValue($value);
    }

    /**
     * @param bool|DateTimeInterface|float|int|string $value Value to compare to.
     */
    final public function withValue(mixed $value): static
    {
        $new = clone $this;
        $new->setValue($value);
        return $new;
    }

    public function toCriteriaArray(): array
    {
        return [static::getOperator(), $this->field, $this->value];
    }

    private function setValue(mixed $value): void
    {
        FilterAssert::isScalarOrInstanceOfDateTimeInterface($value);
        $this->value = $value;
    }
}
