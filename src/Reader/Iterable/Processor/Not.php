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
        if(count($arguments) < 1 || !is_array($arguments[0]) || count($arguments[0]) < 1) {
            throw new \RuntimeException('Invalid arguments!!');
        }
        $operator = array_shift($arguments[0]);
        if (!is_string($operator) || strlen($operator) === '') {
            throw new \RuntimeException('Invalid operator!');
        }

        $processor = $filterProcessors[$operator] ?? null;
        if($processor === null) {
            throw new \RuntimeException(sprintf('"%s" operator is not supported', $operator));
        }
        /* @var $processor IterableProcessorInterface */
        return !$processor->match($item, $arguments[0], $filterProcessors);
    }

}
