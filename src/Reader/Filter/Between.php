<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

use DateTimeInterface;
use Yiisoft\Data\Reader\FilterDataValidationHelper;
use Yiisoft\Data\Reader\FilterInterface;

final class Between implements FilterInterface
{
    private bool|\DateTimeInterface|float|int|string $firstValue;

    private bool|\DateTimeInterface|float|int|string $secondValue;

    /**
     * @param bool|DateTimeInterface|float|int|string $firstValue
     * @param bool|DateTimeInterface|float|int|string $secondValue
     */
    public function __construct(private string $field, $firstValue, $secondValue)
    {
        FilterDataValidationHelper::assertIsScalarOrInstanceOfDataTimeInterface($firstValue);
        FilterDataValidationHelper::assertIsScalarOrInstanceOfDataTimeInterface($secondValue);

        $this->firstValue = $firstValue;
        $this->secondValue = $secondValue;
    }

    public function toCriteriaArray(): array
    {
        return [self::getOperator(), $this->field, $this->firstValue, $this->secondValue];
    }

    public static function getOperator(): string
    {
        return 'between';
    }
}
