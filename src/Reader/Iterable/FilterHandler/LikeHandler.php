<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\FilterHandler;

use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Data\Reader\Filter\Like;
use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\Iterable\IterableFilterHandlerInterface;

use function is_string;

/**
 * `Like` iterable filter handler ensures that the field value is like-match to a given value (case-sensitive).
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
        if (!is_string($itemValue)) {
            return false;
        }

        /** @infection-ignore-all MBString No suitable test case was found yet. */
        return $filter->isCaseSensitive() === true
            ? mb_strpos($itemValue, $filter->getValue()) !== false
            : mb_stripos($itemValue, $filter->getValue()) !== false;
    }
}
