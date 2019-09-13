<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter\Processor\PhpVariable;


class All extends GroupFilter
{

    protected function checkResults(array $results): bool
    {
        return true;
    }

    public function getOperator(): string
    {
        return \Yiisoft\Data\Reader\Filter\All::getOperator();
    }

    protected function checkResult($result): ?bool
    {
        return !$result ? false : null;
    }
}