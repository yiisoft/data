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
        if (count($arguments) < 1 || !is_array($arguments[0])) {
            throw new \RuntimeException('Invalid arguments!');
        }
        $results = [];
        foreach ($arguments[0] as $subFilter) {
            if (!is_array($subFilter) || count($subFilter) < 1) {
                throw new \RuntimeException('Invalid sub filter!');
            }
            $operator = array_shift($subFilter);
            if (!is_string($operator) || strlen($operator) === '') {
                throw new \RuntimeException('Invalid operator!');
            }

            $processor = $filterProcessors[$operator] ?? null;
            if ($processor === null) {
                throw new \RuntimeException(sprintf('Operation "%s" is not supported', $operator));
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
