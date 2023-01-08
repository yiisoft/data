<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\Handler;

use InvalidArgumentException;
use Yiisoft\Data\Reader\FilterDataValidationHelper;

use function array_shift;
use function count;
use function is_array;
use function is_string;
use function sprintf;

abstract class Group implements IterableHandlerInterface
{
    abstract protected function checkResults(array $results): bool;

    public function match(array|object $item, array $arguments, array $filterHandlers): bool
    {
        if (count($arguments) !== 1) {
            throw new InvalidArgumentException('$arguments should contain exactly one element.');
        }

        [$subFilters] = $arguments;

        if (!is_array($subFilters)) {
            throw new InvalidArgumentException(sprintf(
                'The sub filters should be array. The %s is received.',
                FilterDataValidationHelper::getValueType($subFilters),
            ));
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
                    FilterDataValidationHelper::getValueType($operator),
                ));
            }

            if ($operator === '') {
                throw new InvalidArgumentException('The operator string cannot be empty.');
            }

            $filterHandler = $filterHandlers[$operator] ?? null;

            if ($filterHandler === null) {
                throw new InvalidArgumentException(sprintf('"%s" operator is not supported.', $operator));
            }

            FilterDataValidationHelper::assertFilterHandlerIsIterable($filterHandler);
            $results[] = $filterHandler->match($item, $subFilter, $filterHandlers);
        }

        return $this->checkResults($results);
    }
}
