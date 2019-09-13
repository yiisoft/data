<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter\Processor\PhpVariable;


use Yiisoft\Data\Reader\Filter\Processor\PhpVariableFilterProcessor;

abstract class GroupFilter extends Processor
{
    abstract protected function checkResults(array $result): bool;

    abstract protected function checkResult($result): ?bool;

    /**
     * PHP variable specific execute
     */
    function execute(array $item, array $arguments): bool
    {
        $filterProcessor = $this->getFilterProcessor();
        /* @var $filterProcessor PhpVariableFilterProcessor */
        $results = [];
        foreach ($arguments[0] as $subFilter) {
            $result = $filterProcessor->execute($item, $subFilter);
            if(is_bool($this->checkResult($result))) {
                return $result;
            }
            $results[] = $result;
        }

        return $this->checkResults($results);
    }


}