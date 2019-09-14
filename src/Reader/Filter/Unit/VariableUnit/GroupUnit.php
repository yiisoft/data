<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter\Unit\VariableUnit;


use Yiisoft\Data\Reader\Filter\Unit\FilterUnitInterface;

abstract class GroupUnit implements VariableUnitInterface, FilterUnitInterface
{
    abstract protected function checkResults(array $result): bool;

    abstract protected function checkResult($result): ?bool;

    /**
     * PHP variable specific execute
     */
    function match(array $item, array $arguments, array $filterUnits): bool
    {
        $results = [];
        foreach ($arguments[0] as $subFilter) {
            $operation = array_shift($subFilter);

            $unit = $filterUnits[$operation] ?? null;
            if($unit === null) {
                throw new \RuntimeException(sprintf('Operation "%s" is not supported', $operation));
            }
            /* @var $unit \Yiisoft\Data\Reader\Filter\Unit\VariableUnit\VariableUnitInterface */
            $result = $unit->match($item, $subFilter, $filterUnits);
            if(is_bool($this->checkResult($result))) {
                return $result;
            }
            $results[] = $result;
        }

        return $this->checkResults($results);
    }


}