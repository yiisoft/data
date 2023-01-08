<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\IterableFilterHandler;

use InvalidArgumentException;
use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Data\Reader\FilterAssertHelper;
use Yiisoft\Data\Reader\IterableFilterHandlerInterface;

use function count;

abstract class Compare implements IterableFilterHandlerInterface
{
    abstract protected function compare(mixed $itemValue, mixed $argumentValue): bool;

    public function match(array|object $item, array $arguments, array $iterableFilterHandlers): bool
    {
        if (count($arguments) !== 2) {
            throw new InvalidArgumentException('$arguments should contain exactly two elements.');
        }

        [$field, $value] = $arguments;
        FilterAssertHelper::assertFieldIsString($field);

        /** @var string $field */
        return $this->compare(ArrayHelper::getValue($item, $field), $value);
    }
}
