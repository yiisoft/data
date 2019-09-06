<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader\Criterion;

final class Any extends GroupCriterion
{
    protected function getOperator(): string
    {
        return 'any';
    }
}
