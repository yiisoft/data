<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

interface FilterProcessorInterface
{
    public function getOperator(): string;
}
