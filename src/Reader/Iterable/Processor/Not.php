<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\Processor;

use InvalidArgumentException;
use Yiisoft\Data\Reader\Filter\FilterProcessorInterface;
use Yiisoft\Data\Reader\FilterDataValidationHelper;

use function array_shift;
use function count;
use function is_array;
use function is_string;
use function sprintf;

class Not implements IterableProcessorInterface, FilterProcessorInterface
{
    public function getOperator(): string
    {
        return \Yiisoft\Data\Reader\Filter\Not::getOperator();
    }

    public function match(array $item, array $arguments, array $filterProcessors): bool
    {
        if (count($arguments) !== 1) {
            throw new InvalidArgumentException('$arguments should contain exactly one element.');
        }

        [$values] = $arguments;

        if (!is_array($values)) {
            throw new InvalidArgumentException(sprintf(
                'The values should be array. The %s is received.',
                FilterDataValidationHelper::getValueType($values),
            ));
        }

        if (empty($values)) {
            throw new InvalidArgumentException('At least operator should be provided.');
        }

        $operator = array_shift($values);

        if (!is_string($operator)) {
            throw new InvalidArgumentException(sprintf(
                'The operator should be string. The %s is received.',
                FilterDataValidationHelper::getValueType($operator),
            ));
        }

        if ($operator === '') {
            throw new InvalidArgumentException('The operator string cannot be empty.');
        }

        /** @var IterableProcessorInterface|null $filterProcessor */
        $filterProcessor = $filterProcessors[$operator] ?? null;

        if ($filterProcessor === null) {
            throw new InvalidArgumentException(sprintf('"%s" operator is not supported.', $operator));
        }

        FilterDataValidationHelper::assertFilterProcessorIsIterable($filterProcessor);
        return !$filterProcessor->match($item, $values, $filterProcessors);
    }
}
