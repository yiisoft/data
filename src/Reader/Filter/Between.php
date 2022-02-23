<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

use DateTimeInterface;
use Yiisoft\Data\Reader\FilterDataValidationHelper;

final class Between implements FilterInterface
{
    private string $field;

    /**
     * @var bool|DateTimeInterface|float|int|string
     */
    private $firstValue;

    /**
     * @var bool|DateTimeInterface|float|int|string
     */
    private $secondValue;

    /**
     * @param string $field
     * @param bool|DateTimeInterface|float|int|string $firstValue
     * @param bool|DateTimeInterface|float|int|string $secondValue
     */
    public function __construct(string $field, $firstValue, $secondValue)
    {
        $this->field = $field;

        FilterDataValidationHelper::assertIsScalarOrInstanceOfDataTimeInterface($firstValue);
        FilterDataValidationHelper::assertIsScalarOrInstanceOfDataTimeInterface($secondValue);

        $this->firstValue = $firstValue;
        $this->secondValue = $secondValue;
    }

    public function toArray(): array
    {
        return [self::getOperator(), $this->field, $this->firstValue, $this->secondValue];
    }

    public static function getOperator(): string
    {
        return 'between';
    }
}
