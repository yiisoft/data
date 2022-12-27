<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\Processor;

use InvalidArgumentException;
use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Data\Reader\FilterDataValidationHelper;
use Yiisoft\Data\Reader\Iterable\IterableProcessorInterface;

use function count;

final class EqualsNull implements IterableProcessorInterface
{
    public function getOperator(): string
    {
        return \Yiisoft\Data\Reader\Filter\EqualsNull::getOperator();
    }

    public function match(array|object $item, array $arguments, array $filterProcessors): bool
    {
        if (count($arguments) !== 1) {
            throw new InvalidArgumentException('$arguments should contain exactly one element.');
        }

        [$field] = $arguments;
        FilterDataValidationHelper::assertFieldIsString($field);

        /** @var string $field */
        return ArrayHelper::getValue($item, $field) === null;
    }
}
