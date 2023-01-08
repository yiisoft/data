<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

use DateTimeInterface;
use Yiisoft\Data\Reader\FilterAssertHelper;
use Yiisoft\Data\Reader\FilterInterface;

/**
 * Compare filter is a base class that defines a criteria for comparing field value with a given value.
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
        FilterAssertHelper::assertIsScalarOrInstanceOfDateTimeInterface($value);
        $this->value = $value;
    }

    public function toCriteriaArray(): array
    {
        return [static::getOperator(), $this->field, $this->value];
    }
}
