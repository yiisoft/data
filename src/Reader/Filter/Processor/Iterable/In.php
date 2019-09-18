<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter\Processor\Iterable;

use Yiisoft\Data\Reader\Filter\Processor\FilterProcessorInterface;

class In implements IterableProcessorInterface, FilterProcessorInterface
{

    public function getOperator(): string
    {
        return \YiiSoft\Data\Reader\Filter\In::getOperator();
    }

    public function match(array $item, array $arguments, array $filterProcessors): bool
    {
        [$field, $values] = $arguments;
        return in_array($item[$field], $values, false);
    }

}
