<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter\Unit\VariableUnit;

use Yiisoft\Data\Reader\Filter\Unit\FilterUnitInterface;

class Like implements VariableUnitInterface, FilterUnitInterface
{

    public function getOperator(): string
    {
        return \YiiSoft\Data\Reader\Filter\Like::getOperator();
    }

    public function match(array $item, array $arguments, array $filterUnits): bool
    {
        [$field, $value] = $arguments;
        return stripos($item[$field], $value) !== false;
    }

}