<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\Handler;

use InvalidArgumentException;
use Yiisoft\Data\Reader\FilterDataValidationHelper;

use function count;

final class EqualsEmpty implements IterableHandlerInterface
{
    public function getOperator(): string
    {
        return \Yiisoft\Data\Reader\Filter\EqualsEmpty::getOperator();
    }

    public function match(array|object $item, array $arguments, array $filterHandlers): bool
    {
        if (count($arguments) !== 1) {
            throw new InvalidArgumentException('$arguments should contain exactly one element.');
        }

        [$field] = $arguments;
        FilterDataValidationHelper::assertFieldIsString($field);

        /** @var string $field */
        return empty($item[$field]);
    }
}
