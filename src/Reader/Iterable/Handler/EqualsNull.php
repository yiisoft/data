<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\Handler;

use InvalidArgumentException;
use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Data\Reader\FilterDataValidationHelper;

use function count;

final class EqualsNull implements IterableHandlerInterface
{
    public function getOperator(): string
    {
        return \Yiisoft\Data\Reader\Filter\EqualsNull::getOperator();
    }

    public function match(array|object $item, array $arguments, array $filterHandlers): bool
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
