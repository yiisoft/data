<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\Processor;

use Yiisoft\Data\Reader\Filter\FilterProcessorInterface;

class LessThanOrEqual implements IterableProcessorInterface, FilterProcessorInterface
{

    public function getOperator(): string
    {
        return \YiiSoft\Data\Reader\Filter\LessThanOrEqual::getOperator();
    }

    public function match(array $item, array $arguments, array $filterProcessors): bool
    {
        if(count($arguments) < 2) {
            throw new \RuntimeException('The count of arguments is too small!');
        }
        [$field, $value] = $arguments;
        return $item[$field] <= $value;
    }

}
