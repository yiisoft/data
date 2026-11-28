<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\ValueReader;

interface ValueReaderInterface
{
    /**
     * Read a value from an item by the field name.
     *
     * @param array<mixed>|object $item Item to read value from.
     */
    public function read(object|array $item, string $field): mixed;
}
