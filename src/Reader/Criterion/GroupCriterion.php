<?php


namespace Reader\Criterion;


use Yiisoft\Data\Reader\Criterion\CriteronInterface;

abstract class GroupCriterion implements CriteronInterface
{
    /**
     * @var CriteronInterface[]
     */
    private $criteria = [];

    public function __construct(CriteronInterface...$criteria)
    {
        $this->criteria = $criteria;
    }

    public function toArray(): array
    {
        $criteriaArray = [];
        foreach ($this->criteria as $criterion) {
            $criteriaArray[] = $criterion->toArray();
        }
        return [
            $this->getOperator() => $criteriaArray,
        ];
    }

    abstract protected function getOperator(): string;
}
