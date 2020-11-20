<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

interface FilterInterface
{
    public static function getOperator(): string;

    public function toArray(): array;
}
