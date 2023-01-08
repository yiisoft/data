<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

use DateTimeInterface;
use Yiisoft\Data\Reader\FilterDataValidationHelper;
use Yiisoft\Data\Reader\FilterInterface;

abstract class Compare implements FilterInterface
{
    private bool|\DateTimeInterface|float|int|string $value;

    /**
     * @param bool|DateTimeInterface|float|int|string $value
     */
    public function __construct(private string $field, $value)
    {
        FilterDataValidationHelper::assertIsScalarOrInstanceOfDataTimeInterface($value);
        $this->value = $value;
    }

    public function toCriteriaArray(): array
    {
        return [static::getOperator(), $this->field, $this->value];
    }
}
