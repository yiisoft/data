<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter\Unit\VariableUnit;

interface VariableUnitInterface
{
    public function match(array $item, array $arguments, array $filterUnits): bool;
}