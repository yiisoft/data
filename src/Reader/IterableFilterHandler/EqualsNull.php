<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\IterableFilterHandler;

use InvalidArgumentException;
use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Data\Reader\FilterDataValidationHelper;
use Yiisoft\Data\Reader\IterableFilterHandlerInterface;

use function count;

final class EqualsNull implements IterableFilterHandlerInterface
{
    public function getOperator(): string
    {
        return \Yiisoft\Data\Reader\Filter\EqualsNull::getOperator();
    }

    public function match(array|object $item, array $arguments, array $iterableFilterHandlers): bool
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