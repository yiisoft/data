<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

use Yiisoft\Data\Reader\FilterDataValidationHelper;

final class In implements FilterInterface
{
    private string $field;

    /**
     * @var bool[]|float[]|int[]|string[]
     */
    private array $values;

    /**
     * @param string $field
     * @param bool[]|float[]|int[]|string[] $values
     */
    public function __construct(string $field, array $values)
    {
        foreach ($values as $value) {
            FilterDataValidationHelper::assertIsScalar($value);
        }

        $this->field = $field;
        $this->values = $values;
    }

    public function toArray(): array
    {
        return [self::getOperator(), $this->field, $this->values];
    }

    public static function getOperator(): string
    {
        return 'in';
    }
}
