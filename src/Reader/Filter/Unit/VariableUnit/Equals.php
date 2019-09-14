<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter\Unit\VariableUnit;

use Yiisoft\Data\Reader\Filter\Unit\FilterUnitInterface;

class Equals implements VariableUnitInterface, FilterUnitInterface
{

    public function getOperator(): string
    {
        return \YiiSoft\Data\Reader\Filter\Equals::getOperator();
    }

    public function match(array $item, array $arguments, array $filterUnits): bool
    {
        [$field, $value] = $arguments;
        return $item[$field] == $value;
    }

}