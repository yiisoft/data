<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

final class Like implements FilterInterface
{
    private string $field;
    private string $value;

    public function __construct(string $field, string $value)
    {
        $this->field = $field;
        $this->value = $value;
    }

    public function toArray(): array
    {
        return [self::getOperator(), $this->field, $this->value];
    }

    public static function getOperator(): string
    {
        return 'like';
    }
}
