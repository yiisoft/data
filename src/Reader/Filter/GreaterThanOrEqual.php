<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

final class GreaterThanOrEqual extends CompareFilter
{
    public static function getOperator(): string
    {
        return '>=';
    }
}
