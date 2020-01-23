<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

final class All extends GroupFilter
{
    public static function getOperator(): string
    {
        return 'and';
    }
}
