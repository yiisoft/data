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

        $searchValue = (string) $filter->value;
        if ($searchValue === '') {
            return true;
        }

        return match ($filter->mode) {
            LikeMode::Contains => $this->matchContains($itemValue, $searchValue, $filter->caseSensitive),
            LikeMode::StartsWith => $this->matchStartsWith($itemValue, $searchValue, $filter->caseSensitive),
            LikeMode::EndsWith => $this->matchEndsWith($itemValue, $searchValue, $filter->caseSensitive),
        };
    }

    private function matchContains(string $value, string $search, ?bool $caseSensitive): bool
    {
        return $caseSensitive === true
            ? str_contains($value, $search)
            : mb_stripos($value, $search) !== false;
    }

    private function matchStartsWith(string $value, string $search, ?bool $caseSensitive): bool
    {
        return $caseSensitive === true
            ? str_starts_with($value, $search)
            : mb_stripos($value, $search) === 0;
    }

    private function matchEndsWith(string $value, string $search, ?bool $caseSensitive): bool
    {
        if ($caseSensitive === true) {
            return str_ends_with($value, $search);
        }

        return mb_strtolower(mb_substr($value, -mb_strlen($search))) === mb_strtolower($search);
    }
}
