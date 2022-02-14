<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\Processor;

use InvalidArgumentException;
use Yiisoft\Data\Reader\Filter\FilterProcessorInterface;
use Yiisoft\Data\Reader\FilterDataValidationHelper;

use function array_key_exists;
use function count;

class Between implements IterableProcessorInterface, FilterProcessorInterface
{
    public function getOperator(): string
    {
        return \Yiisoft\Data\Reader\Filter\Between::getOperator();
    }

    public function match(array $item, array $arguments, array $filterProcessors): bool
    {
        if (count($arguments) !== 3) {
            throw new InvalidArgumentException('$arguments should contain exactly three elements.');
        }

        [$field, $firstValue, $secondValue] = $arguments;
        FilterDataValidationHelper::validateFieldValueType($field);

        /** @var string $field */
        return array_key_exists($field, $item) && $item[$field] >= $firstValue && $item[$field] <= $secondValue;
    }
}
