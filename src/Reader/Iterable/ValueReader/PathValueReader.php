<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\ValueReader;

use Yiisoft\Arrays\ArrayHelper;

final class PathValueReader implements ValueReaderInterface
{
    public function __construct(
        private readonly string $delimiter = '.',
    ) {
    }

    public function read(object|array $item, string $field): mixed
    {
        return ArrayHelper::getValueByPath($item, $field, $this->delimiter);
    }
}
