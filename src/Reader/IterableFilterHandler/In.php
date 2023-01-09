<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\IterableFilterHandler;

use function in_array;
use function is_array;

/**
 * In iterable filter handler ensures that the field value matches one of the value provided.
 */
final class In extends Compare
{
    public function getOperator(): string
    {
        return \Yiisoft\Data\Reader\Filter\In::getOperator();
    }

    protected function compare(mixed $itemValue, mixed $argumentValue): bool
    {
        return is_array($argumentValue) && in_array($itemValue, $argumentValue, false);
    }
}
