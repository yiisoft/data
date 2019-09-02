<?php


namespace Yiisoft\Data\Reader\Criterion;


use Reader\Criterion\GroupCriterion;

final class OrAny extends GroupCriterion
{
    protected function getOperator(): string
    {
        return 'or';
    }
}
