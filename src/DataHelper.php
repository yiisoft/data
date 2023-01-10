<?php

declare(strict_types=1);

namespace Yiisoft\Data;

use function is_object;

final class DataHelper
{
    public static function getValue(array|object $item, string $field): mixed
    {
        if (is_object($item)) {
            /** @psalm-suppress MixedMethodCall */
            return str_ends_with($field, '()')
                ? $item->{substr($field, 0, -2)}()
                : $item->$field;
        }

        return $item[$field];
    }
}
