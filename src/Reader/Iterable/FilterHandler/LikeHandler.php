<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\FilterHandler;

use Yiisoft\Data\Reader\Filter\Like;
use Yiisoft\Data\Reader\Filter\LikeMode;
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

        return match ($filter->mode) {
            LikeMode::CONTAINS => $this->matchContains($itemValue, $filter->value, $filter->caseSensitive),
            LikeMode::STARTS_WITH => $this->matchStartsWith($itemValue, $filter->value, $filter->caseSensitive),
            LikeMode::ENDS_WITH => $this->matchEndsWith($itemValue, $filter->value, $filter->caseSensitive),
        };
    }

    private function matchContains(string $itemValue, string $searchValue, ?bool $caseSensitive): bool
    {
        return $caseSensitive === true
            ? str_contains($itemValue, $searchValue)
            : mb_stripos($itemValue, $searchValue) !== false;
    }

    private function matchStartsWith(string $itemValue, string $searchValue, ?bool $caseSensitive): bool
    {
        return $caseSensitive === true
            ? str_starts_with($itemValue, $searchValue)
            : mb_stripos($itemValue, $searchValue) === 0;
    }

    private function matchEndsWith(string $itemValue, string $searchValue, ?bool $caseSensitive): bool
    {
        if ($caseSensitive === true) {
            return str_ends_with($itemValue, $searchValue);
        }
        
        $searchLength = mb_strlen($searchValue);
        if ($searchLength > mb_strlen($itemValue)) {
            return false;
        }
        
        return mb_strtolower(mb_substr($itemValue, -$searchLength)) === mb_strtolower($searchValue);
    }
}
