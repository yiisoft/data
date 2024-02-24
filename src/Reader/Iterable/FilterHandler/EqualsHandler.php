<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\FilterHandler;

use DateTimeInterface;
use InvalidArgumentException;
use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Data\Reader\Filter\Equals;
use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\Iterable\IterableFilterHandlerInterface;

/**
 * `Equals` iterable filter handler checks that the item's field value matches given value.
 */
final class EqualsHandler implements IterableFilterHandlerInterface
{
    public function getFilterClass(): string
    {
        return Equals::class;
    }

    public function match(object|array $item, FilterInterface $filter, array $iterableFilterHandlers): bool
    {
        /** @var Equals $filter */

        $itemValue = ArrayHelper::getValue($item, $filter->field);
        $argumentValue = $filter->getValue();

        if (!$itemValue instanceof DateTimeInterface) {
            return $itemValue == $argumentValue;
        }

        return $argumentValue instanceof DateTimeInterface
            && $itemValue->getTimestamp() === $argumentValue->getTimestamp();
    }
}
