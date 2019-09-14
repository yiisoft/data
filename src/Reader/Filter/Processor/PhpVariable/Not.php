<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter\Processor\PhpVariable;

use Yiisoft\Data\Reader\Filter\Processor\PhpVariableFilterProcessor;

class Not extends Processor
{

    public function getOperator(): string
    {
        return \YiiSoft\Data\Reader\Filter\Not::getOperator();
    }

    public function match(array $item, array $arguments): bool
    {
        $filterProcessor = $this->getFilterProcessor();
        /* @var $filterProcessor PhpVariableFilterProcessor */
        return !$filterProcessor->match($item, $arguments[0]);
    }

}