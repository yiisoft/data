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
        $results = [];
        foreach ($arguments[0] as $subFilter) {
            $operation = array_shift($subFilter);

            $processor = $filterProcessors[$operation] ?? null;
            if ($processor === null) {
                throw new \RuntimeException(sprintf('Operation "%s" is not supported', $operation));
            }
            /* @var $processor IterableProcessorInterface */
            $result = $processor->match($item, $subFilter, $filterProcessors);
            if(is_bool($this->checkResult($result))) {
                return $result;
            }
            $results[] = $result;
        }

        return $this->checkResults($results);
    }


}
