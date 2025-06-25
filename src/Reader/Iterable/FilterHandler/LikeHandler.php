<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\FilterHandler;

use Yiisoft\Data\Reader\Filter\Like;
use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\Iterable\Context;
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

    public function match(object|array $item, FilterInterface $filter, Context $context): bool
    {
        /** @var Like $filter */

        $itemValue = $context->readValue($item, $filter->getField());
        if (!is_string($itemValue)) {
            return false;
        }

        return $filter->getCaseSensitive() === true
            ? str_contains($itemValue, $filter->getValue())
            : mb_stripos($itemValue, $filter->getValue()) !== false;
    }
}
