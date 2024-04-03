<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader;

use function implode;
use function preg_split;
use function substr;
use function trim;

/**
 * @psalm-import-type TOrder from Sort
 */
final class OrderHelper
{
    /**
     * Create fields order array from an order string.
     *
     * The string consists of comma-separated field names.
     * If the name is prefixed with `-`, field order is descending.
     * Otherwise, the order is ascending.
     *
     * @param string $orderString Logical fields order as comma-separated string.
     *
     * @return array Logical fields order as array.
     *
     * @psalm-return TOrder
     */
    public static function stringToArray(string $orderString): array
    {
        $order = [];
        $parts = preg_split('/\s*,\s*/', trim($orderString), -1, PREG_SPLIT_NO_EMPTY);

        foreach ($parts as $part) {
            if (str_starts_with($part, '-')) {
                $order[substr($part, 1)] = 'desc';
            } else {
                $order[$part] = 'asc';
            }
        }

        return $order;
    }

    /**
     * Create an order string based on logical fields order array.
     *
     * The string consists of comma-separated field names.
     * If the name is prefixed with `-`, field order is descending.
     * Otherwise, the order is ascending.
     *
     * @param array $order Logical fields order as array.
     *
     * @return string An order string.
     */
    public static function arrayToString(array $order): string
    {
        $parts = [];
        foreach ($order as $field => $direction) {
            $parts[] = ($direction === 'desc' ? '-' : '') . $field;
        }

        return implode(',', $parts);
    }

    /**
     * Replace field name in logical fields order array.
     *
     * @param array $order Logical fields order as array.
     * @param string $from Field name to replace.
     * @param string $to Field name to replace with.
     */
    public static function replaceFieldName(array &$order, string $from, string $to): void
    {
        if ($from === $to) {
            return;
        }

        if (!isset($order[$from])) {
            return;
        }

        $order[$to] = $order[$from];
        unset($order[$from]);
    }
}
