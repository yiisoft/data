<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

final class Not implements FilterInterface
{
    private $criterion;

    public function __construct(FilterInterface $criterion)
    {
        $this->criterion = $criterion;
    }

    public function toArray(): array
    {
        return [self::getOperator(), $this->criterion->toArray()];
    }

    public static function getOperator(): string
    {
        return 'not';
    }
}
