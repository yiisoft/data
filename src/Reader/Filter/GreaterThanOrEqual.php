<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

final class GreaterThanOrEqual extends CompareCriterion
{
    public static function getOperator(): string
    {
        return 'gte';
    }
}
