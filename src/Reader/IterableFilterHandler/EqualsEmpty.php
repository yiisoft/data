<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\IterableFilterHandler;

use InvalidArgumentException;
use Yiisoft\Data\Reader\FilterAssertHelper;
use Yiisoft\Data\Reader\IterableFilterHandlerInterface;

use function count;

final class EqualsEmpty implements IterableFilterHandlerInterface
{
    public function getOperator(): string
    {
        return \Yiisoft\Data\Reader\Filter\EqualsEmpty::getOperator();
    }

    public function match(array|object $item, array $arguments, array $iterableFilterHandlers): bool
    {
        if (count($arguments) !== 1) {
            throw new InvalidArgumentException('$arguments should contain exactly one element.');
        }

        [$field] = $arguments;
        FilterAssertHelper::assertFieldIsString($field);

        /** @var string $field */
        return empty($item[$field]);
    }
}
