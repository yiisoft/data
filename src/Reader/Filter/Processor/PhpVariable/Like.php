<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter\Processor\PhpVariable;

class Like extends Processor
{

    public function getOperator(): string
    {
        return \YiiSoft\Data\Reader\Filter\Like::getOperator();
    }

    public function match(array $item, array $arguments): bool
    {
        [$field, $value] = $arguments;
        return stripos($item[$field], $value) !== false;
    }

}