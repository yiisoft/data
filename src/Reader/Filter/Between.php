<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

use DateTimeInterface;
use Yiisoft\Data\Reader\FilterDataValidationHelper;

final class Between implements FilterInterface
{
    private string $field;

    /**
     * @var bool|float|int|string|DateTimeInterface
     */
    private $firstValue;

    /**
     * @var bool|float|int|string|DateTimeInterface
     */
    private $secondValue;

    /**
     * @param string $field
     * @param bool|float|int|string|DateTimeInterface $firstValue
     * @param bool|float|int|string|DateTimeInterface $secondValue
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
