<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\FilterHandler;

use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Data\Reader\Filter\Like;
use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\Iterable\IterableFilterHandlerInterface;

use function is_string;
use function stripos;

/**
 * `Like` iterable filter handler ensures that the field value is like-match to a given value.
 */
final class LikeHandler implements IterableFilterHandlerInterface
{
    public function getFilterClass(): string
    {
        return Like::class;
    }

    public function match(object|array $item, FilterInterface $filter, array $iterableFilterHandlers): bool
    {
        /** @var Like $filter */

        $itemValue = ArrayHelper::getValue($item, $filter->getField());

        return is_string($itemValue) && stripos($itemValue, $filter->getValue()) !== false;
    }
}
