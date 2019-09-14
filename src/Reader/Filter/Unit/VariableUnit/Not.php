<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter\Unit\VariableUnit;

use Yiisoft\Data\Reader\Filter\Unit\FilterUnitInterface;

class Not implements VariableUnitInterface, FilterUnitInterface
{

    public function getOperator(): string
    {
        return \YiiSoft\Data\Reader\Filter\Not::getOperator();
    }

    public function match(array $item, array $arguments, array $filterUnits): bool
    {
        $operation = array_shift($arguments[0]);

        $unit = $filterUnits[$operation] ?? null;
        if($unit === null) {
            throw new \RuntimeException(sprintf('Operation "%s" is not supported', $operation));
        }
        /* @var $unit \Yiisoft\Data\Reader\Filter\Unit\VariableUnit\VariableUnitInterface */
        return !$unit->match($item, $arguments[0], $filterUnits);
    }

}