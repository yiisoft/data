<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\IterableFilterHandler;

use function is_string;
use function stripos;

final class Like extends Compare
{
    public function getOperator(): string
    {
        return \Yiisoft\Data\Reader\Filter\Like::getOperator();
    }

    protected function compare(mixed $itemValue, mixed $argumentValue): bool
    {
        return is_string($itemValue) && is_string($argumentValue) && stripos($itemValue, $argumentValue) !== false;
    }
}