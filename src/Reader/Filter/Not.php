<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

final class Not implements FilterInterface
{
    private FilterInterface $filter;

    public function __construct(FilterInterface $filter)
    {
        $this->filter = $filter;
    }

    public function toArray(): array
    {
        return [self::getOperator(), $this->filter->toArray()];
    }

    public static function getOperator(): string
    {
        return 'not';
    }
}
