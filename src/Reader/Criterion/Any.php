<?php


namespace Yiisoft\Data\Reader\Criterion;


use Reader\Criterion\GroupCriterion;

final class Any extends GroupCriterion
{
    protected function getOperator(): string
    {
        return 'or';
    }
}
