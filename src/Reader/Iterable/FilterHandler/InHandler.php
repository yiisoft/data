<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\FilterHandler;

use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Data\Reader\Filter\In;
use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\Iterable\IterableFilterHandlerInterface;

use function in_array;

/**
 * `In` iterable filter handler ensures that the field value matches one of the value provided.
 */
final class InHandler implements IterableFilterHandlerInterface
{
    public function getFilterClass(): string
    {
        return In::class;
    }

    public function match(object|array $item, FilterInterface $filter, array $iterableFilterHandlers): bool
    {
        /** @var In $filter */

        $itemValue = ArrayHelper::getValue($item, $filter->field);
        $argumentValue = $filter->getValues();

        return in_array($itemValue, $argumentValue);
    }
}
