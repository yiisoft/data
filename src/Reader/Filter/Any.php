<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

final class Any extends Group
{
    public static function getOperator(): string
    {
        return 'or';
    }
}
