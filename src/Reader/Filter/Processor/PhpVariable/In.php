<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter\Processor\PhpVariable;

class In extends Processor
{

    public function getOperator(): string
    {
        return \YiiSoft\Data\Reader\Filter\In::getOperator();
    }

    public function match(array $item, array $arguments): bool
    {
        [$field, $values] = $arguments;
        return in_array($item[$field], $values, false);
    }

}