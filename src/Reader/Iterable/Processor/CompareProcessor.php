<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\Processor;

use InvalidArgumentException;
use Yiisoft\Data\Reader\Filter\FilterProcessorInterface;
use Yiisoft\Data\Reader\FilterDataValidationHelper;

use function array_key_exists;
use function count;

abstract class CompareProcessor implements IterableProcessorInterface, FilterProcessorInterface
{
    /**
     * @param mixed $itemValue
     * @param mixed $argumentValue
     */
    abstract protected function compare($itemValue, $argumentValue): bool;

    public function match(array $item, array $arguments, array $filterProcessors): bool
    {
        if (count($arguments) !== 2) {
            throw new InvalidArgumentException('$arguments should contain exactly two elements.');
        }

        [$field, $value] = $arguments;
        FilterDataValidationHelper::assertFieldIsString($field);

        /** @var string $field */
        return array_key_exists($field, $item) && $this->compare($item[$field], $value);
    }
}
