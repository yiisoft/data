<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter\Unit;


interface FilterUnitInterface
{
    public function getOperator(): string;
}