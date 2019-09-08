<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

final class GreaterThan extends CompareCriterion
{
    public static function getOperator(): string
    {
        return 'gt';
    }
}
