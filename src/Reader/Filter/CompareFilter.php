<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

use DateTimeInterface;
use Yiisoft\Data\Reader\FilterDataValidationHelper;

abstract class CompareFilter implements FilterInterface
{
    private string $field;

    /**
     * @var bool|DateTimeInterface|float|int|string
     */
    private $value;

    /**
     * @param bool|DateTimeInterface|float|int|string $value
     */
    public function __construct(string $field, $value)
    {
        FilterDataValidationHelper::assertIsScalarOrInstanceOfDataTimeInterface($value);

        $this->field = $field;
        $this->value = $value;
    }

    public function toArray(): array
    {
        return [static::getOperator(), $this->field, $this->value];
    }
}
