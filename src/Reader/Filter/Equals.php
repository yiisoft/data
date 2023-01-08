<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

final class Equals extends Compare
{
    public static function getOperator(): string
    {
        return '=';
    }
}
