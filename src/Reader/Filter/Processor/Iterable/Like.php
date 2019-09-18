<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter\Processor\Iterable;

use Yiisoft\Data\Reader\Filter\Processor\FilterProcessorInterface;

class Like implements IterableProcessorInterface, FilterProcessorInterface
{

    public function getOperator(): string
    {
        return \YiiSoft\Data\Reader\Filter\Like::getOperator();
    }

    public function match(array $item, array $arguments, array $filterProcessors): bool
    {
        [$field, $value] = $arguments;
        return stripos($item[$field], $value) !== false;
    }

}
