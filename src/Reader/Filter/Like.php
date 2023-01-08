<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

use Yiisoft\Data\Reader\FilterInterface;

final class Like implements FilterInterface
{
    public function __construct(private string $field, private string $value)
    {
    }

    public function toCriteriaArray(): array
    {
        return [self::getOperator(), $this->field, $this->value];
    }

    public static function getOperator(): string
    {
        return 'like';
    }
}
