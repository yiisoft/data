<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter\Processor\PhpVariable;

class LessThan extends Processor
{

    public function getOperator(): string
    {
        return \YiiSoft\Data\Reader\Filter\LessThan::getOperator();
    }

    public function match(array $item, array $arguments): bool
    {
        [$field, $value] = $arguments;
        return $item[$field] < $value;
    }

}