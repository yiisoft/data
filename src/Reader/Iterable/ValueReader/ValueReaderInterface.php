<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\ValueReader;

interface ValueReaderInterface
{
    public function read(object|array $item, string $field): mixed;
}
