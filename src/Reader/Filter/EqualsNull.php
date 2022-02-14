<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

final class EqualsNull implements FilterInterface
{
    private string $field;

    public function __construct(string $field)
    {
        $this->field = $field;
    }

    public function toArray(): array
    {
        return [self::getOperator(), $this->field];
    }

    public static function getOperator(): string
    {
        return 'null';
    }
}
