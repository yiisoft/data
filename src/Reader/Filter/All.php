<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

final class All extends GroupCriterion
{
    public static function getOperator(): string
    {
        return 'all';
    }
}
