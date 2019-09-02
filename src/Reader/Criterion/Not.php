<?php


namespace Yiisoft\Data\Reader\Criterion;


final class Not implements CriteronInterface
{
    private $criterion;

    public function __construct(CriteronInterface $criterion)
    {
        $this->criterion = $criterion;
    }

    public function toArray(): array
    {
        return ['not', $this->criterion->toArray()];
    }
}
