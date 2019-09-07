<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader\Criterion;

final class All extends GroupCriterion
{
    public static function getOperator(): string
    {
        return 'all';
    }
}
