<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\Processor;

use InvalidArgumentException;
use Yiisoft\Data\Reader\Filter\FilterProcessorInterface;
use Yiisoft\Data\Reader\FilterDataValidationHelper;

use function array_key_exists;
use function count;
use function get_class;
use function gettype;
use function is_object;
use function sprintf;

class In implements IterableProcessorInterface, FilterProcessorInterface
{
    public function getOperator(): string
    {
        return \Yiisoft\Data\Reader\Filter\In::getOperator();
    }

    public function match(array $item, array $arguments, array $filterProcessors): bool
    {
        if (count($arguments) !== 2) {
            throw new InvalidArgumentException('$arguments should contain exactly two elements.');
        }

        [$field, $values] = $arguments;
        FilterDataValidationHelper::validateFieldValueType($field);

        if (!is_array($values)) {
            throw new InvalidArgumentException(sprintf(
                'The values should be array. The %s is received.',
                is_object($values) ? get_class($values) : gettype($values),
            ));
        }

        /** @var string $field */
        return array_key_exists($field, $item) && in_array($item[$field], $values, false);
    }
}
