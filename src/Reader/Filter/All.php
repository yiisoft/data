<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

final class All extends Group
{
    public static function getOperator(): string
    {
        return 'and';
    }
}
