<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

class Equals extends CompareFilter
{
    public static function getOperator(): string
    {
        return '=';
    }
}
