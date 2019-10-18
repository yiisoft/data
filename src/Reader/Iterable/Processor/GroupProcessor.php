<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\Processor;


use Yiisoft\Data\Reader\Filter\FilterProcessorInterface;

abstract class GroupProcessor implements IterableProcessorInterface, FilterProcessorInterface
{
    abstract protected function checkResults(array $result): bool;

    abstract protected function checkResult($result): ?bool;

    /**
     * PHP variable specific execute
     */
    public function match(array $item, array $arguments, array $filterProcessors): bool
    {
        if (count($arguments) < 1) {
            throw new \RuntimeException('At least one argument should be provided!');
        } elseif (!is_array($arguments[0])) {
            throw new \RuntimeException('Sub filters is not an array!');
        }
        $results = [];
        foreach ($arguments[0] as $subFilter) {
            if (!is_array($subFilter)) {
                throw new \RuntimeException('Sub filter is not an array!');
            } elseif (count($subFilter) < 1) {
                throw new \RuntimeException('At least operator should be provided!');
            }
            $operator = array_shift($subFilter);
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
            $result = $processor->match($item, $subFilter, $filterProcessors);
            if (is_bool($this->checkResult($result))) {
                return $result;
            }
            $results[] = $result;
        }

        return $this->checkResults($results);
    }


}
