<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\Processor;

class All extends GroupProcessor
{
    public function getOperator(): string
    {
        return \Yiisoft\Data\Reader\Filter\All::getOperator();
    }

    protected function checkResults(array $results): bool
    {
        return true;
    }

    protected function checkResult(bool $result): ?bool
    {
        return !$result ? false : null;
    }
}
