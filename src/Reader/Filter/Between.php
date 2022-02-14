<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

use Yiisoft\Data\Reader\FilterDataValidationHelper;

final class Between implements FilterInterface
{
    private string $field;

    /**
     * @var bool|float|int|string
     */
    private $firstValue;

    /**
     * @var bool|float|int|string
     */
    private $secondValue;

    /**
     * @param string $field
     * @param bool|float|int|string $firstValue
     * @param bool|float|int|string $secondValue
     */
    public function __construct(string $field, $firstValue, $secondValue)
    {
        $this->field = $field;

        FilterDataValidationHelper::validateScalarValueType($firstValue);
        FilterDataValidationHelper::validateScalarValueType($secondValue);

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
