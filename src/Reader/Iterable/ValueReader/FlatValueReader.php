<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\ValueReader;

use Yiisoft\Arrays\ArrayHelper;

final class FlatValueReader implements ValueReaderInterface
{
    public function read(object|array $item, string $field): mixed
    {
        return ArrayHelper::getValue($item, $field);
    }
}
