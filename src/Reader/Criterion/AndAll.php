<?php


namespace Yiisoft\Data\Reader\Criterion;


use Reader\Criterion\GroupCriterion;

final class AndAll extends GroupCriterion
{
    protected function getOperator(): string
    {
        return 'and';
    }
}
