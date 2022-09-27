<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

final class EqualsEmpty implements FilterInterface
{
    public function __construct(private string $field)
    {
    }

    public function toArray(): array
    {
        return [self::getOperator(), $this->field];
    }

    public static function getOperator(): string
    {
        return 'empty';
    }
}
