<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\Processor;

use Yiisoft\Data\Reader\Filter\FilterProcessorInterface;

class Not implements IterableProcessorInterface, FilterProcessorInterface
{

    public function getOperator(): string
    {
        return \YiiSoft\Data\Reader\Filter\Not::getOperator();
    }

    public function match(array $item, array $arguments, array $filterProcessors): bool
    {
        $operation = array_shift($arguments[0]);

        $processor = $filterProcessors[$operation] ?? null;
        if($processor === null) {
            throw new \RuntimeException(sprintf('Operation "%s" is not supported', $operation));
        }
        /* @var $processor IterableProcessorInterface */
        return !$processor->match($item, $arguments[0], $filterProcessors);
    }

}
