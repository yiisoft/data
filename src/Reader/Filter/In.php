<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

use Yiisoft\Data\Reader\FilterDataValidationHelper;

final class In implements FilterInterface
{
    private string $field;
    private array $value;

    /**
     * @param string $field
     * @param bool[]|float[]|int[]|string[] $value
     */
    public function __construct(string $field, array $value)
    {
        foreach ($value as $arrayValue) {
            FilterDataValidationHelper::validateScalarValueType($arrayValue);
        }

        $this->field = $field;
        $this->value = $value;
    }

    public function toArray(): array
    {
        return [self::getOperator(), $this->field, $this->value];
    }

    public static function getOperator(): string
    {
        return 'in';
    }
}
