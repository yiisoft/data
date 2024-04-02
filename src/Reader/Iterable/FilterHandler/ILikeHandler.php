<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\FilterHandler;

use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Data\Reader\Filter\ILike;
use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\Iterable\IterableFilterHandlerInterface;

use function is_string;

/**
 * `ILike` iterable filter handler ensures that the field value is like-match to a given value (case-insensitive).
 */
final class ILikeHandler implements IterableFilterHandlerInterface
{
    public function getFilterClass(): string
    {
        return ILike::class;
    }

    public function match(object|array $item, FilterInterface $filter, array $iterableFilterHandlers): bool
    {
        /** @var ILike $filter */

        $itemValue = ArrayHelper::getValue($item, $filter->getField());

        return is_string($itemValue) && mb_stripos($itemValue, $filter->getValue()) !== false;
    }
}
