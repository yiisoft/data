<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\FilterHandler;

use InvalidArgumentException;
use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Data\Reader\Filter\EqualsEmpty;
use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\Iterable\IterableFilterHandlerInterface;

/**
 * `EqualsEmpty` iterable filter handler checks that the item's field value is empty.
 */
final class EqualsEmptyHandler implements IterableFilterHandlerInterface
{
    public function getFilterClass(): string
    {
        return EqualsEmpty::class;
    }

    public function match(array|object $item, FilterInterface $filter, array $iterableFilterHandlers): bool
    {
        if (!$filter instanceof EqualsEmpty) {
            throw new InvalidArgumentException('Incorrect filter.');
        }

        $value = ArrayHelper::getValue($item, $filter->field);

        return empty($value);
    }
}
