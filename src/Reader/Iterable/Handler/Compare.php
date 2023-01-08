<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\Handler;

use InvalidArgumentException;
use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Data\Reader\FilterDataValidationHelper;

use function count;

abstract class Compare implements IterableHandlerInterface
{
    abstract protected function compare(mixed $itemValue, mixed $argumentValue): bool;

    public function match(array|object $item, array $arguments, array $filterProcessors): bool
    {
        if (count($arguments) !== 2) {
            throw new InvalidArgumentException('$arguments should contain exactly two elements.');
        }

        [$field, $value] = $arguments;
        FilterDataValidationHelper::assertFieldIsString($field);

        /** @var string $field */
        return $this->compare(ArrayHelper::getValue($item, $field), $value);
    }
}
