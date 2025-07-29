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

        $itemValue = $context->readValue($item, $filter->field);
        if (!is_string($itemValue)) {
            return false;
        }

        return $filter->caseSensitive === true
            ? str_contains($itemValue, $filter->value)
            : mb_stripos($itemValue, $filter->value) !== false;
    }
}
