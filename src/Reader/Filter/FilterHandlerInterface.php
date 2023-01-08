<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

interface FilterHandlerInterface
{
    public function getOperator(): string;
}
