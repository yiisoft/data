<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\Processor;

use InvalidArgumentException;
use Yiisoft\Data\Reader\Filter\FilterProcessorInterface;
use Yiisoft\Data\Reader\FilterDataValidationHelper;

use function array_shift;
use function is_array;
use function is_bool;
use function is_string;
use function sprintf;

abstract class GroupProcessor implements IterableProcessorInterface, FilterProcessorInterface
{
    abstract protected function checkResults(array $results): bool;

    abstract protected function checkResult(bool $result): ?bool;

    public function match(array $item, array $arguments, array $filterProcessors): bool
    {
        if (empty($arguments)) {
            throw new InvalidArgumentException('At least one argument should be provided.');
        }

        [$subFilters] = $arguments;

        if (!is_array($subFilters)) {
            throw new InvalidArgumentException('Sub filters is not an array.');
        }

        $results = [];

        foreach ($subFilters as $subFilter) {
            if (!is_array($subFilter)) {
                throw new InvalidArgumentException(sprintf(
                    'The sub filter should be array. The %s is received.',
                    FilterDataValidationHelper::getValueType($subFilter),
                ));
            }

            if (empty($subFilter)) {
                throw new InvalidArgumentException('At least operator should be provided.');
            }

            $operator = array_shift($subFilter);

            if (!is_string($operator)) {
                throw new InvalidArgumentException(sprintf(
                    'The operator should be string. The %s is received.',
                    FilterDataValidationHelper::getValueType($subFilter),
                ));
            }

            if ($operator === '') {
                throw new InvalidArgumentException('The operator string cannot be empty.');
            }

            /** @var IterableProcessorInterface|null $processor */
            $processor = $filterProcessors[$operator] ?? null;

            if ($processor === null) {
                throw new InvalidArgumentException(sprintf('"%s" operator is not supported.', $operator));
            }

            $result = $processor->match($item, $subFilter, $filterProcessors);

            if (is_bool($this->checkResult($result))) {
                return $result;
            }

            $results[] = $result;
        }

        return $this->checkResults($results);
    }
}
