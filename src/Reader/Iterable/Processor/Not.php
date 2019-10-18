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
        if (count($arguments) !== 1) {
            throw new \RuntimeException('$arguments should contain exactly one element!');
        } elseif (!is_array($arguments[0])) {
            throw new \RuntimeException('$arguments[0] is not an array!');
        } elseif (count($arguments[0]) < 1) {
            throw new \RuntimeException('At least operator should be provided!');
        }
        $operator = array_shift($arguments[0]);
        if (!is_string($operator)) {
            throw new \RuntimeException('Operator is not a string!');
        } elseif (strlen($operator) === 0) {
            throw new \RuntimeException('The operator string cannot be empty!');
        }

        $processor = $filterProcessors[$operator] ?? null;
        if ($processor === null) {
            throw new \RuntimeException(sprintf('"%s" operator is not supported!', $operator));
        }
        /* @var $processor IterableProcessorInterface */
        return !$processor->match($item, $arguments[0], $filterProcessors);
    }

}
