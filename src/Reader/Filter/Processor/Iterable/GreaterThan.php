<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter\Processor\Iterable;

use Yiisoft\Data\Reader\Filter\Processor\FilterProcessorInterface;

class GreaterThan implements IterableProcessorInterface, FilterProcessorInterface
{

    public function getOperator(): string
    {
        return \YiiSoft\Data\Reader\Filter\GreaterThan::getOperator();
    }

    public function match(array $item, array $arguments, array $filterProcessors): bool
    {
        [$field, $value] = $arguments;
        return $item[$field] > $value;
    }

}
