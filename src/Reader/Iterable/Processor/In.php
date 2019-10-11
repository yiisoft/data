<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\Processor;

use Yiisoft\Data\Reader\Filter\FilterProcessorInterface;

class In implements IterableProcessorInterface, FilterProcessorInterface
{

    public function getOperator(): string
    {
        return \YiiSoft\Data\Reader\Filter\In::getOperator();
    }

    public function match(array $item, array $arguments, array $filterProcessors): bool
    {
        if(count($arguments) < 2) {
            throw new \RuntimeException('The count of arguments is too small!');
        }
        [$field, $values] = $arguments;
        if(!is_array($values)) {
            throw new \RuntimeException('The values not an array!');
        }
        return in_array($item[$field], $values, false);
    }

}
