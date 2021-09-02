<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

use InvalidArgumentException;

abstract class CompareFilter implements FilterInterface
{
    private string $field;

    /**
     * @var bool|float|int|string
     */
    private $value;

    /**
     * @param mixed $value
     */
    public function __construct(string $field, $value)
    {
        if (!is_scalar($value)) {
            throw new InvalidArgumentException('Value should be scalar');
        }

        $this->field = $field;
        $this->value = $value;
    }

    public function toArray(): array
    {
        return [static::getOperator(), $this->field, $this->value];
    }
}
