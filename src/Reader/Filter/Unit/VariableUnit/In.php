<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter\Unit\VariableUnit;

use Yiisoft\Data\Reader\Filter\Unit\FilterUnitInterface;

class In implements VariableUnitInterface, FilterUnitInterface
{

    public function getOperator(): string
    {
        return \YiiSoft\Data\Reader\Filter\In::getOperator();
    }

    public function match(array $item, array $arguments, array $filterUnits): bool
    {
        [$field, $values] = $arguments;
        return in_array($item[$field], $values, false);
    }

}