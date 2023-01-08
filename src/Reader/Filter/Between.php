<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

use DateTimeInterface;
use Yiisoft\Data\Reader\FilterAssertHelper;
use Yiisoft\Data\Reader\FilterInterface;

/**
 * Between filter defines a criteria so the value of the field with a given name
 * is between the minimal value and the maximal value.
 */
final class Between implements FilterInterface
{
    private bool|DateTimeInterface|float|int|string $minimalValue;

    private bool|DateTimeInterface|float|int|string $maximalValue;

    /**
     * @param string $field Name of the field to compare.
     * @param bool|DateTimeInterface|float|int|string $minimumValue Minimal field value.
     * @param bool|DateTimeInterface|float|int|string $maximumValue Maximal field value.
     */
    public function __construct(private string $field, mixed $minimumValue, mixed $maximumValue)
    {
        FilterAssertHelper::assertIsScalarOrInstanceOfDateTimeInterface($minimumValue);
        FilterAssertHelper::assertIsScalarOrInstanceOfDateTimeInterface($maximumValue);

        $this->minimalValue = $minimumValue;
        $this->maximalValue = $maximumValue;
    }

    public function toCriteriaArray(): array
    {
        return [self::getOperator(), $this->field, $this->minimalValue, $this->maximalValue];
    }

    public static function getOperator(): string
    {
        return 'between';
    }
}
