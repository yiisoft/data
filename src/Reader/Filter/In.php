<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

use Yiisoft\Data\Reader\FilterDataValidationHelper;
use Yiisoft\Data\Reader\FilterInterface;

final class In implements FilterInterface
{
    /**
     * @var bool[]|float[]|int[]|string[]
     */
    private array $values;

    /**
     * @param bool[]|float[]|int[]|string[] $values
     */
    public function __construct(private string $field, array $values)
    {
        foreach ($values as $value) {
            FilterDataValidationHelper::assertIsScalar($value);
        }
        $this->values = $values;
    }

    public function toCriteriaArray(): array
    {
        return [self::getOperator(), $this->field, $this->values];
    }

    public static function getOperator(): string
    {
        return 'in';
    }
}
