<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

use Yiisoft\Data\Reader\FilterInterface;

final class EqualsNull implements FilterInterface
{
    public function __construct(private string $field)
    {
    }

    public function toCriteriaArray(): array
    {
        return [self::getOperator(), $this->field];
    }

    public static function getOperator(): string
    {
        return 'null';
    }
}
